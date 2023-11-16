<?php

namespace App\Controller;

use App\Content\Event\Data\EventCreateData;
use App\Content\Event\EventManager;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaCreateData;
use App\Content\SecretSanta\SecretSantaService;
use App\Entity\Secret;
use App\Entity\SecretSantaEvent;
use App\Form\SecretSantaCreateFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/secret-santa')]
#[IsGranted('ROLE_USER')]
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

        return $this->render('secret_santa/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/detail/{event}', name: 'app_secret_santa_detail')]
    public function detail(SecretSantaEvent $event): Response
    {
        $secret = new Secret();
        return $this->render('secret_santa/detail.html.twig', [
            'event' => $event,
            'user' => $this->getUser(),
            'secret' => $secret,
        ]);
    }
}
