<?php

namespace App\Controller;

use App\Content\Event\Data\EventCreateData;
use App\Content\Event\Data\EventData;
use App\Content\Event\EventService;
use App\Content\Event\EventType;
use App\Entity\Event;
use App\Form\EventCreateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/event')]
class EventController extends AbstractController
{


    public function __construct(private EventService $eventService)
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

            $this->eventService->initEvent($data, $this->getUser());

            return $this->redirect($this->generateUrl('app_home', []));
        }

        return $this->render('event/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/contribute/{event}', name: 'app_event_contribute')]
    public function contribute(Event $event): Response
    {
        $this->getUser();

        $data = (new EventData())->initFromEntity($event);

        $this->eventService->addParticipant($event, $this->getUser());

        return new JsonResponse([true]);
    }
}
