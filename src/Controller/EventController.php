<?php

namespace App\Controller;

use App\Content\Desire\DesireManager;
use App\Content\DesireList\DesireListService;
use App\Content\Event\Data\EventCreateData;
use App\Content\Event\Data\EventData;
use App\Content\Event\EventManager;
use App\Content\Event\EventService;
use App\Content\Event\EventType;
use App\Content\SecretSanta\SecretSantaEvent\SecretSantaEventService;
use App\Entity\Event;
use App\Entity\User;
use App\Form\EventCreateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    #[Route('/', name: 'app_event_list')]
    public function list(EventService $eventService, SecretSantaEventService $secretSantaEventService): Response
    {
        $events = $eventService->findEventsWithoutSecretSantaRounds();
        $secretSantaEvents = $secretSantaEventService->findBy([]);

        return $this->render('event/home.html.twig', [
            'user' => $this->getUser(),
            'events' => $events,
            'secretSantaEvents' => $secretSantaEvents,
        ]);
    }

    #[Route('/create', name: 'app_event_create')]
    public function create(Request $request): Response
    {
        $data = new EventCreateData();
        $form = $this->createForm(EventCreateType::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var EventCreateData $data */
            $data = $form->getData();

            $this->eventManager->initBirthdayEvent($data, $this->getUser());

            return $this->redirect($this->generateUrl('app_event_list', []));
        }

        return $this->render('event/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ajax routes
    #[Route('/join/{event}', name: 'app_event_join')]
    public function contribute(Event $event): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            throw new \Exception('User not found');
        }

        $this->eventManager->addParticipant($event, $user);
        $user = $this->getUser();

        $roles = [];
        if ($user instanceof User){
            $roles[] = $user->getAccessRoles()->toArray();
        }

        return new RedirectResponse($this->generateUrl('app_event_detail', ['event' => $event->getId()]));
    }

    #[Route('/exit/{event}', name: 'app_event_exit')]
    public function exit(Event $event): Response
    {
        $this->getUser();

        $this->eventManager->removeParticipant($event, $this->getUser());

        return new JsonResponse([true]);
    }

    #[Route('/detail/{event}', name: 'app_event_detail')]
    public function manage(Event $event, DesireListService $desireListService): Response
    {
        $userDesireList = $event->getDesireLists()->first();

        if ($userDesireList->getOwner() !== $this->getUser()){
            return $this->redirect($this->generateUrl('app_desire_list', ['desireList' => $userDesireList->getId()]));
        }

        return $this->render('event/detail.html.twig', [
            'event' => $event,
            'userDesireList' => $userDesireList,
        ]);
    }
}
