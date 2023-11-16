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

        $events = $eventService->findEventsWithoutSecretSantaRounds();
        $secretSantaEvents = $secretSantaEventService->findBy([]);

        return $this->render('landing/index.html.twig', [
            'user' => $this->getUser(),
            'events' => $events,
            'secretSantaEvents' => $secretSantaEvents,
        ]);
    }
}
