<?php

namespace App\Controller;

use App\Content\Event\Data\EventCreateData;
use App\Content\Event\EventManager;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaCreateData;
use App\Content\SecretSanta\SecretSantaService;
use App\Content\SecretSanta\SecretSantaState;
use App\Entity\Secret;
use App\Entity\SecretSantaEvent;
use App\Form\SecretSantaCreateFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/secret-santa')]
//#[IsGranted('ROLE_USER')]
class SecretSantaController extends AbstractController
{


    public function __construct(private SecretSantaService $secretSantaService)
    {
    }

    #[Route('/create', name: 'app_secret_santa_create')]
    public function index(Request $request, EventManager $eventManager): Response
    {
        $data = new SecretSantaCreateData();
        $form = $this->createForm(SecretSantaCreateFormType::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SecretSantaCreateData $data */
            $data = $form->getData();

            $eventManager->initSecretSantaEvent($data, $this->getUser());
            return $this->redirect($this->generateUrl('app_home', []));
        }

        return $this->render('secret_santa/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/detail/{event}', name: 'app_secret_santa_detail')]
    public function detail(SecretSantaEvent $event): Response
    {
        $stateText = sprintf(
            'Das Event "%s" wurde noch nicht gestartet. Wir informieren dich sobald es los geht. Leg derweil doch schon mal ein paar eigene Wünsche an!',
            $event->getName()
        );

        if ($event->getState() === SecretSantaState::PHASE_1) {
            $stateText = sprintf(
                'Und weiter gehts. Ziehe hier deinen Wichtel für "%s"',
                $event->getFirstRound()->getName()
            );
            if ($this->secretSantaService->userHasAlreadyPickedSecretForEvent($event->getFirstRound(), $this->getUser())) {
                $stateText = sprintf(
                    'Du hast deinen Wichtel für %s bereits. Sobald alle anderen gezogen haben, starten wir Runde 2',
                    $event->getFirstRound()->getName()
                );
            }
        }

        if ($event->getState() === SecretSantaState::PHASE_2) {
            $stateText = sprintf(
                'Und weiter gehts. Ziehe hier deinen Wichtel für "%s"',
                $event->getSecondRound()->getName()
            );
            if ($this->secretSantaService->userHasAlreadyPickedSecretForEvent($event->getSecondRound(), $this->getUser())) {
                $stateText = sprintf(
                    'Du hast deinen Wichtel für %s bereits. Sobald alle anderen gezogen haben, kann gewichtelt werden',
                    $event->getSecondRound()->getName()
                );
            }
        }

        if ($event->getState() === SecretSantaState::RUNNING) {
            $stateText = 'Es wurde gewichtelt was das Zeug hält. Der sorting hat ist leer gezogen. Jetzt heißt es Geschenke kaufen!';
            if ($this->secretSantaService->userHasAlreadyPickedSecretForEvent($event->getSecondRound(), $this->getUser())) {
                $stateText = sprintf(
                    'Du hast deinen Wichtel für %s bereits. Sobald alle anderen gezogen haben, kann gewichtelt werden',
                    $event->getSecondRound()->getName()
                );
            }
        }

        $secret = new Secret();
        return $this->render('secret_santa/detail.html.twig', [
            'event' => $event,
            'user' => $this->getUser(),
            'secret' => $secret,
            'stateText' => $stateText,
        ]);
    }
}
