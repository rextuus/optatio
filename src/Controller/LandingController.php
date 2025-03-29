<?php

namespace App\Controller;

use App\Content\Event\EventService;
use App\Content\SecretSanta\SecretSantaEvent\SecretSantaEventService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class LandingController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EventService $eventService, SecretSantaEventService $secretSantaEventService): Response
    {
        $user = $this->getUser();
        $events = $eventService->findEventsWithoutSecretSantaRounds($user);
        $secretSantaEvents = $secretSantaEventService->findSecretSantaEvents($user);

        $ownEvents = [];
        $ownSecretSantaEvents = [];
        $participatingEvents = [];
        $participatingSecretSantaEvents = [];
        foreach ($events as $event){
            if ($event->getCreator() === $user){
                $ownEvents[] = $event;
            }
            elseif ($event->getParticipants()->contains($user)){
                $participatingEvents[] = $event;
            }
        }

        foreach ($secretSantaEvents as $secretSantaEvent){
            if ($secretSantaEvent->getCreator() === $user){
                $ownSecretSantaEvents[] = $secretSantaEvent;
            }
            if (in_array($user, $secretSantaEvent->getOverallParticipants())){
                $participatingSecretSantaEvents[] = $secretSantaEvent;
            }
        }

        return $this->render('landing/home.html.twig', [
            'user' => $this->getUser(),
            'ownEvents' => $ownEvents,
            'ownSecretSantaEvents' => $ownSecretSantaEvents,
            'participatingEvents' => $participatingEvents,
            'participatingSecretSantaEvents' => $participatingSecretSantaEvents,
            'events' => $events,
            'secretSantaEvents' => $secretSantaEvents,
        ]);
    }
}
