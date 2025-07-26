<?php

namespace App\Controller;

use App\Content\Desire\ImageExtraction\ExtractPicsApiService;
use App\Content\Event\EventService;
use App\Content\SecretSanta\SecretSantaEvent\SecretSantaEventService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        $events = $eventService->findEventsWithoutSecretSantaRounds();
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
            if (in_array($user, $secretSantaEvent->getOverallParticipants()) && $secretSantaEvent->getFirstRound()->getCreator() !== $user ){
                $participatingSecretSantaEvents[] = $secretSantaEvent;
            }
        }

        $bookmarks = $user->getEventBookmarks();

        $bookmarkedEvents = [];
        $bookmarkedSecretSantaEvents = [];
        foreach ($bookmarks as $bookmark){
            if ($bookmark->getSecretSantaEvent() !== null){
                $bookmarkedSecretSantaEvents[] = $bookmark->getSecretSantaEvent();
            }else{
                $bookmarkedEvents[] = $bookmark->getEvent();
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
            'bookmarkedEvents' => $bookmarkedEvents,
            'bookmarkedSecretSantaEvents' => $bookmarkedSecretSantaEvents
        ]);
    }

    #[Route('/scrape', name: 'app_scape_test')]
    public function fetchImages(Request $request, ExtractPicsApiService $extractPicsApiService)
    {
        $url = $request->query->get('url');

        if (!$url) {
            return new JsonResponse(['error' => 'URL not provided'], 400);
        }
//9f261106-ac5a-4819-bba2-9d1ba24b5198
        $images = $extractPicsApiService->startExtraction($url);
        dd($images);

        return $images;
    }

}
