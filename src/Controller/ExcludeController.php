<?php

namespace App\Controller;

use App\Content\SecretSanta\Exclusion\Data\ExclusionData;
use App\Content\SecretSanta\Exclusion\ExclusionService;
use App\Entity\Exclusion;
use App\Entity\SecretSantaEvent;
use App\Form\ExcludeCreateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/exclusion')]
class ExcludeController extends BaseController
{
    public function __construct(private ExclusionService $exclusionService)
    {
    }

    #[Route('/create/{event}', name: 'app_exclusion_create')]
    public function create(Request $request, SecretSantaEvent $event): Response
    {
        $user = $this->getLoggedInUser();
        $data = new ExclusionData();
        $data->setExclusionCreator($user);
        $data->setEvent($event);
        $data->setBidirectional(false);
        $form = $this->createForm(
            ExcludeCreateType::class,
            $data,
            [
                'currentUser' => $user,
                'event' => $event,
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ExclusionData $data */
            $data = $form->getData();

            if ($data->getExcludedUser()){
                $this->exclusionService->createByData($data);
            }
            return $this->redirect($this->generateUrl('app_exclusion_create', ['event' => $event->getId()]));
        }

        $exclusions = $this->exclusionService->findBy(['event' => $event, 'exclusionCreator' => $user]);

        return $this->render('secret/index.html.twig', [
            'form' => $form->createView(),
            'exclusions' => $exclusions,
            'event' => $event,
        ]);
    }

    #[Route('/delete/{event}/{exclusion}', name: 'app_exclusion_delete')]
    public function delete(Exclusion $exclusion, SecretSantaEvent $event): Response
    {
        $user = $this->getLoggedInUser();
        if ($exclusion->getExclusionCreator() !== $user){
            return $this->redirect($this->generateUrl('app_event_list', []));
        }

        $this->exclusionService->delete($exclusion);

        return $this->redirect($this->generateUrl('app_exclusion_create', ['event' => $event->getId()]));
    }
}
