<?php

namespace App\Controller;

use App\Content\Desire\DesireState;
use App\Entity\Desire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/desire')]
class DesireController extends AbstractController
{
    #[Route('/me', name: 'app_desire_me')]
    public function index(): Response
    {
        $desire = new Desire();
        $desire->setName('Kuchen');
        $desire->setUrl('https://test.de');
        $desire->setExclusive(false);
        $desire->setExactly(false);
        $desire->setPriority(1);
        $desire->setState(DesireState::FREE);

        return $this->render('desire/index.html.twig', [
            'desire' => $desire,
        ]);
    }
}
