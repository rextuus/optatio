<?php

namespace App\Controller;

use App\Content\Event\Data\EventCreateData;
use App\Content\Event\Data\EventData;
use App\Content\Event\EventManager;
use App\Content\Event\EventService;
use App\Content\Event\EventType;
use App\Entity\Event;
use App\Form\EventCreateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/event')]
#[IsGranted('ROLE_USER')]
class EventController extends AbstractController
{


    public function __construct(private EventManager $eventManager)
    {
    }

    #[Route('/create', name: 'app_event_list')]
    public function index(Request $request): Response
    {
        $data = new EventCreateData();
        $form = $this->createForm(EventCreateType::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var EventCreateData $data */
            $data = $form->getData();

            $this->eventManager->initEvent($data, $this->getUser());

            return $this->redirect($this->generateUrl('app_home', []));
        }

        return $this->render('event/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ajax routes
    #[Route('/join/{event}', name: 'app_event_join')]
    public function contribute(Event $event): Response
    {
        $this->getUser();

        $this->eventManager->addParticipant($event, $this->getUser());

        return new JsonResponse([true]);
    }

    #[Route('/exit/{event}', name: 'app_event_exit')]
    public function exit(Event $event): Response
    {
        $this->getUser();

        $this->eventManager->removeParticipant($event, $this->getUser());

        return new JsonResponse([true]);
    }

    #[Route('/manage/{event}', name: 'app_event_manage')]
    public function manage(Event $event): Response
    {
        $data = new EventCreateData();
        $form = $this->createForm(EventCreateType::class, $data);

        return $this->render('event/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
