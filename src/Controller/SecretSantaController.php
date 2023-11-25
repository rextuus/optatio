<?php

namespace App\Controller;

use App\Content\Desire\DesireManager;
use App\Content\Event\EventManager;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventJoinData;
use App\Content\SecretSanta\SecretSantaService;
use App\Content\SecretSanta\SecretSantaState;
use App\Entity\Event;
use App\Entity\Secret;
use App\Entity\SecretSantaEvent;
use App\Entity\User;
use App\Form\SecretSantaCreateFormType;
use App\Form\SecretSantaEventJoinType;
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
        $data = new SecretSantaCreateData();
        $form = $this->createForm(SecretSantaCreateFormType::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SecretSantaEventJoinData $data */
            $data = $form->getData();

            $eventManager->initSecretSantaEvent($data, $this->getUser());
            return $this->redirect($this->generateUrl('app_home', []));
        }

        return $this->render('secret_santa/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/join/{event}', name: 'app_secret_santa_join')]
    public function join(SecretSantaEvent $event, Request $request): Response
    {
        $data = new SecretSantaEventJoinData();
        $form = $this->createForm(SecretSantaEventJoinType::class, $data);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var SecretSantaEventJoinData $data */
            $data = $form->getData();

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
        $secondRoundActive = $this->checkUserIsParticipantOfEvent($participant, $event->getSecondRound());

        $showFirstRoundPick = false;
        $showSecondRoundPick = false;
        $secrets = $this->secretSantaService->getSecretsForUser($event, $participant);

        $stateText = sprintf(
            'Das Event "%s" wurde noch nicht gestartet. Wir informieren dich sobald es los geht. Leg derweil doch schon mal ein paar eigene Wünsche an!',
            $event->getName()
        );

        if ($event->getState() === SecretSantaState::PHASE_1 && $firstRoundActive) {
            $showFirstRoundPick = true;
            $stateText = sprintf(
                'Und los gehts. Ziehe hier deinen Wichtel für <b>%s</b>',
                $event->getFirstRound()->getName()
            );
            if ($secrets['first']->isRetrieved()) {
                $showFirstRoundPick = false;
                $stateText = sprintf(
                    'Du hast deinen Wichtel für %s bereits. Sobald alle anderen gezogen haben, starten wir Runde 2',
                    $event->getFirstRound()->getName()
                );
            }
        }

        if ($event->getState() === SecretSantaState::PHASE_2) {
            if ($firstRoundActive){
                $stateText = sprintf(
                    'Du hast deinen Wichtel für "%s" bereits gezogen. Es ist %s. Sobald es weiter geht siehst du hier seine Wunschliste',
                    $event->getFirstRound()->getName(),
                    $secrets['first']->getReceiver()->getFirstName(),
                );
            }

            if ($secondRoundActive){
                $showSecondRoundPick = true;

                $stateText = sprintf(
                    'Und weiter gehts. Ziehe hier deinen Wichtel für "%s"',
                    $event->getSecondRound()->getName()
                );
                if ($secrets['second']->isRetrieved()) {
                    $showSecondRoundPick = false;
                    $stateText = sprintf(
                        'Du hast deinen Wichtel für "%s" bereits gezogen. Es ist %s. Sobald es weiter geht siehst du hier seine Wunschliste',
                        $event->getSecondRound()->getName(),
                        $secrets['second']->getReceiver()->getFirstName(),
                    );

                    if ($firstRoundActive) {
                        $stateText = sprintf(
                            'Du hast deine Wichtel für "%s" und "%s" bereits gezogen. Es sind %s und %s. Sobald es weiter geht siehst du hier ihre Wunschlisten',
                            $event->getFirstRound()->getName(),
                            $event->getSecondRound()->getName(),
                            $secrets['first']->getReceiver()->getFirstName(),
                            $secrets['second']->getReceiver()->getFirstName(),
                        );
                    }
                }
            }
        }

        $firstRoundList = null;
        $secondRoundList = null;
        if ($event->getState() === SecretSantaState::RUNNING) {
            if ($firstRoundActive){
                $firstRoundList = $this->desireManager->getDesireListForSecretSantaEvent($secrets['first']->getReceiver(), $event);
            }
            if ($secondRoundActive){
                $secondRoundList = $this->desireManager->getDesireListForSecretSantaEvent($secrets['second']->getReceiver(), $event);
            }

            $stateText = 'Es wurde gewichtelt was das Zeug hält. Der sorting hat ist leer gezogen. Jetzt heißt es Geschenke kaufen!';
        }

        // TODO need to get the eventdesirelist from current user and the ones of its secrets
        $userDesireList = $this->desireManager->getDesireListForSecretSantaEvent($this->getUser(), $event);


        $secret = new Secret();
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
        ]);
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
