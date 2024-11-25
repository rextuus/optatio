<?php

namespace App\Controller;

use App\Content\Desire\DesireManager;
use App\Content\Event\EventManager;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventCreateData;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventData;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventJoinData;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaStartData;
use App\Content\SecretSanta\SecretSantaService;
use App\Content\SecretSanta\SecretSantaState;
use App\Entity\AccessRole;
use App\Entity\Event;
use App\Entity\Secret;
use App\Entity\SecretSantaEvent;
use App\Entity\User;
use App\Form\GodfatherAddType;
use App\Form\SecretSantaCreateFormType;
use App\Form\SecretSantaEventJoinType;
use App\Form\SecretSantaStartType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/secret-santa')]
#[IsGranted('ROLE_USER')]
class SecretSantaController extends BaseController
{
    public function __construct(private SecretSantaService $secretSantaService, private EventManager $eventManager, private DesireManager $desireManager)
    {
    }

    #[Route('/create', name: 'app_secret_santa_create')]
    public function index(Request $request, EventManager $eventManager): Response
    {
        $data = new SecretSantaEventCreateData();
        $form = $this->createForm(SecretSantaCreateFormType::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SecretSantaEventCreateData $data */
            $data = $form->getData();

            $eventManager->initSecretSantaEvent($data, $this->getUser());
            return $this->redirect($this->generateUrl('app_event_list', []));
        }

        return $this->render('secret_santa/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/join/{event}', name: 'app_secret_santa_join')]
    public function join(SecretSantaEvent $event, Request $request): Response
    {
        $data = new SecretSantaEventJoinData();
        if (!$event->isIsDoubleRound()){
            $data->setFirstRound(true);
        }
        $form = $this->createForm(SecretSantaEventJoinType::class, $data);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            if (!$data->isFirstRound() && !$data->isSecondRound()){
                return $this->redirect($this->generateUrl('app_event_list', []));
            }

            $this->eventManager->addParticipantToSecretSantaEvent($this->getUser(), $event, $data);

            return $this->redirect($this->generateUrl('app_secret_santa_detail', ['event' => $event->getId()]));
        }


        return $this->render('secret_santa/join.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/pick/first/{event}/{user}', name: 'app_secret_santa_pick_first')]
    public function pickFirst(SecretSantaEvent $event, User $user): Response
    {
        $this->secretSantaService->performFirstRoundPick($event, $user);
        return new JsonResponse(true);
    }

    #[Route('/pick/second/{event}/{user}', name: 'app_secret_santa_pick_second')]
    public function pickSecond(SecretSantaEvent $event, User $user): Response
    {
        $this->secretSantaService->performSecondRoundPick($event, $user);
        return new JsonResponse(true);
    }

    #[Route('/detail/{event}', name: 'app_secret_santa_detail')]
    public function detail(SecretSantaEvent $event): Response
    {
        $participant = $this->getLoggedInUser();
        $firstRoundActive = $this->checkUserIsParticipantOfEvent($participant, $event->getFirstRound());

        $secondRoundActive = $event->isIsDoubleRound();
        if ($secondRoundActive){
            $secondRoundActive = $this->checkUserIsParticipantOfEvent($participant, $event->getSecondRound());
        }

        $showFirstRoundPick = false;
        $showSecondRoundPick = false;
        $secrets = $this->secretSantaService->getSecretsForUser($event, $participant);
        $secret = null;

        $stateText = sprintf(
            'Das Event "%s" wurde noch nicht gestartet. Wir informieren dich sobald es los geht. Leg derweil doch schon mal ein paar eigene Wünsche an!',
            $event->getName()
        );

        if ($event->getState() === SecretSantaState::PHASE_1 && $firstRoundActive) {
            $secret = $secrets['first'];

            $showFirstRoundPick = true;
            $stateText = sprintf(
                'Und los gehts. Ziehe hier deinen Wichtel für <b><span class="ss-event-text-name">%s</span></b>',
                $event->getFirstRound()->getName()
            );
            if ($secrets['first']->isRetrieved()) {
                $showFirstRoundPick = false;

                $stateText = sprintf(
                    'Du hast deinen Wichtel für <span class="ss-event-text-name"><span class="ss-event-text-name">%s</span></span> bereits gezogen. Es ist <span class="ss-event-text-name">%s</span>. Sobald alle anderen gezogen haben, kannst du dir die Wunschliste deines Wichtels anschauen</span>',
                    $event->getFirstRound()->getName(),
                    $secrets['first']->getReceiver()->getFirstName()
                );

                if ($event->isIsDoubleRound()){
                    $stateText = sprintf(
                        'Du hast deinen Wichtel für <span class="ss-event-text-name"><span class="ss-event-text-name">%s</span></span> bereits. Es ist <span class="ss-event-text-name">%s</span>. Sobald alle anderen gezogen haben, starten wir mit der Ziehung für <span class="ss-event-text-name">%s</span>',
                        $event->getFirstRound()->getName(),
                        $secrets['first']->getReceiver()->getFirstName(),
                        $event->getSecondRound()->getName(),
                    );
                }
            }
        }

        if ($event->getState() === SecretSantaState::PHASE_2) {
            if ($firstRoundActive){
                $stateText = sprintf(
                    'Du hast deinen Wichtel für "<span class="ss-event-text-name">%s</span>" bereits gezogen. Es ist <span class="ss-event-text-name">%s</span>. Sobald es weiter geht siehst du hier seine Wunschliste',
                    $event->getFirstRound()->getName(),
                    $secrets['first']->getReceiver()->getFirstName(),
                );
            }

            if ($secondRoundActive){
                $showSecondRoundPick = true;
                $secret = $secrets['second'];
                $stateText = sprintf(
                    'Und weiter gehts. Ziehe hier deinen Wichtel für "<span class="ss-event-text-name">%s</span>"',
                    $event->getSecondRound()->getName()
                );
                if ($secrets['second']->isRetrieved()) {
                    $showSecondRoundPick = false;
                    $stateText = sprintf(
                        'Du hast deinen Wichtel für "<span class="ss-event-text-name">%s</span>" bereits gezogen. Es ist <span class="ss-event-text-name">%s</span>. Sobald es weiter geht siehst du hier seine Wunschliste',
                        $event->getSecondRound()->getName(),
                        $secrets['second']->getReceiver()->getFirstName(),
                    );

                    if ($firstRoundActive) {
                        $stateText = sprintf(
                            'Du hast deine Wichtel für "<span class="ss-event-text-name">%s</span>" und "<span class="ss-event-text-name">%s</span>" bereits gezogen. Es sind <span class="ss-event-text-name">%s</span> und <span class="ss-event-text-name">%s</span>. Sobald es weiter geht siehst du hier ihre Wunschlisten',
                            $event->getFirstRound()->getName(),
                            $event->getSecondRound()->getName(),
                            $secrets['first']->getReceiver()->getFirstName(),
                            $secrets['second']->getReceiver()->getFirstName(),
                        );
                    }
                }
            }
        }

        $isGodfather = $this->checkUserIsGodFatherOfSecretSantaEvent($participant, $event);
        $firstRoundList = null;
        $secondRoundList = null;
        if ($event->getState() === SecretSantaState::RUNNING) {;
            if ($firstRoundActive && !$isGodfather){
                $firstRoundList = $this->desireManager->getDesireListForSecretSantaEvent($secrets['first']->getReceiver(), $event);
            }
            if ($secondRoundActive && !$isGodfather){
                $secondRoundList = $this->desireManager->getDesireListForSecretSantaEvent($secrets['second']->getReceiver(), $event);
            }

            $stateText = 'Es wurde gewichtelt was das Zeug hält. Der sorting hat ist leer gezogen. Jetzt heißt es Geschenke kaufen!</br>';

            if ($isGodfather){
                $stateText = 'Als Godfather dieses Events kannst du auf die Wunschlisten aller Teilnehmer zugreifen!</br>';
            }

            if ($firstRoundActive && !$isGodfather) {
                $stateText = $stateText . sprintf(
                        'Dein Wichtel für <span class="ss-event-text-name">%s</span> ist <span class="ss-event-text-name">%s</span>. ',
                        $event->getFirstRound()->getName(),
                        $secrets['first']->getReceiver()->getFirstName(),
                    );
            }

            if ($secondRoundActive && !$isGodfather) {
                $stateText = $stateText . sprintf(
                        'Dein Wichtel für <span class="ss-event-text-name">%s</span> ist <span class="ss-event-text-name">%s</span>.',
                        $event->getSecondRound()->getName(),
                        $secrets['second']->getReceiver()->getFirstName(),
                    );
            }
        }

        // TODO need to get the eventdesirelist from current user and the ones of its secrets
        $userDesireList = $this->desireManager->getDesireListForSecretSantaEvent($this->getUser(), $event);

        $resolvedSecretsFirstRound = $this->secretSantaService->getSecretStatisticForEvent($event);

        $godFatherDesireLists = [];
        foreach ($event->getGodFathers() as $godFather){
            $godFatherDesireLists[] = $this->desireManager->getDesireListForSecretSantaEvent($godFather, $event);
        }

        if ($isGodfather){
            $godFatherDesireLists = [];
            foreach ($event->getOverallParticipants() as $participant){
                if ($participant->getId() !== $this->getUser()->getId()){
                    $godFatherDesireLists[] = $this->desireManager->getDesireListForSecretSantaEvent($participant, $event);
                }
            }
        }

        return $this->render('secret_santa/detail.html.twig', [
            'event' => $event,
            'user' => $this->getUser(),
            'ownList' => $userDesireList,
            'secret' => $secret,
            'stateText' => $stateText,
            'showPick' => $showFirstRoundPick || $showSecondRoundPick,
            'showFirstRoundPick' => $showFirstRoundPick,
            'showSecondRoundPick' => $showSecondRoundPick,
            'firstRoundList' => $firstRoundList,
            'secondRoundList' => $secondRoundList,
            'statistic' => $resolvedSecretsFirstRound,
            'isOwner' => $this->checkUserIsOwnerOfSecretSantaEvent($participant, $event),
            'godFatherDesireLists' => $godFatherDesireLists,
        ]);
    }

    #[Route('/admin/{event}', name: 'app_secret_santa_admin')]
    public function admin(SecretSantaEvent $event, Request $request): Response
    {
        return $this->render('secret_santa/admin.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/godfather/{event}', name: 'app_secret_santa_godfather')]
    public function godfather(SecretSantaEvent $event, Request $request): Response
    {

        $form = $this->createForm(GodfatherAddType::class, [], ['secret_santa_event' => $event]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData()['user'];
            $this->secretSantaService->addGodfather($user, $event);
        }

        return $this->render('secret_santa/godfather.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/start/{event}', name: 'app_secret_santa_start')]
    public function start(SecretSantaEvent $event, Request $request): Response
    {
        $data = new SecretSantaStartData();
        $form = $this->createForm(SecretSantaStartType::class, $data);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SecretSantaStartData $data */
            $data = $form->getData();
            if ($data->getCheckSum() === 'sss'){
                $this->secretSantaService->triggerCalculation($event);
            }

            return $this->redirect($this->generateUrl('app_secret_santa_start', ['event' => $event->getId()]));
        }

        $round1 = $event->getFirstRound()->getParticipants()->toArray();

        $round2 = null;
        if ($event->isIsDoubleRound()){
            $round2 = $event->getSecondRound()->getParticipants()->toArray();
        }
        $exclusions = $event->getExclusions()->toArray();

        $result = $this->secretSantaService->testCalculation($event);

        $showForm = $event->getState() === SecretSantaState::OPEN;
        return $this->render('secret_santa/start.html.twig', [
            'event' => $event,
            'result' => $result,
            'form' => $form,
            'round1' => $round1,
            'round2' => $round2,
            'exclusions' => $exclusions,
            'showForm' => $showForm,
        ]);
    }

    private function checkUserIsOwnerOfSecretSantaEvent(User $user, SecretSantaEvent $event): bool
    {
        $ownerRoleOne = 'ROLE_EVENT_' . $event->getFirstRound()->getId() . '_OWNER';

        $isOwnerFirstRound = count($user->getAccessRoles()->filter(
            function (AccessRole $accessRole) use ($ownerRoleOne) {
                return $accessRole->getIdent() === $ownerRoleOne;
            }
        ));

        $isOwnerSecondRound = false;
        if ($event->isIsDoubleRound()){
            $ownerRoleSecond = 'ROLE_EVENT_' . $event->getSecondRound()->getId() . '_OWNER';
            $isOwnerSecondRound = count($user->getAccessRoles()->filter(
                function (AccessRole $accessRole) use ($ownerRoleSecond) {
                    return $accessRole->getIdent() === $ownerRoleSecond;
                }
            ));
        }

        return $isOwnerFirstRound || $isOwnerSecondRound;
    }

    private function checkUserIsGodFatherOfSecretSantaEvent(User $user, SecretSantaEvent $event): bool
    {
        return count(array_filter(
            $event->getGodfathers()->toArray(),
            function (User $godfather) use ($user) {
                return $godfather->getId() === $user->getId();
            }
        )) > 0;
    }

    private function checkUserIsParticipantOfEvent(User $user, Event $targetEvent): bool
    {
        return count($user->getEvents()->filter(
            function (Event $event) use ($targetEvent) {
                return $event->getId() === $targetEvent->getId();
            }
        ));
    }
}
