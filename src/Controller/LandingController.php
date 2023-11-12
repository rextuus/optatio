<?php

namespace App\Controller;

use App\Content\Event\EventService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class LandingController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EventService $eventService): Response
    {

        $events = $eventService->findBy([]);

        return $this->render('landing/index.html.twig', [
            'user' => $this->getUser(),
            'events' => $events,
        ]);
    }
}
