<?php

namespace App\Controller;

use App\Content\Desire\Data\DesireData;
use App\Content\Desire\DesireManager;
use App\Content\DesireList\DesireListService;
use App\Content\SecretSanta\SecretSantaEvent\SecretSantaEventService;
use App\Entity\Desire;
use App\Entity\DesireList;
use App\Form\DesireCreateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/desire')]
#[IsGranted('ROLE_USER')]
class DesireController extends BaseController
{


    public function __construct(
        private DesireManager $desireManager,
        private DesireListService $desireListService,
        private SecretSantaEventService $secretSantaEventService,
    )
    {
    }

    #[Route('/list/{desireList}', name: 'app_desire_list')]
    public function index(DesireList $desireList): Response
    {
        dump($desireList->getAccessRoles()->toArray());
        dump($this->getUser()->getAccessRoles()->toArray());

        if ($desireList->getOwner() === $this->getLoggedInUser()){
            $desires = $this->desireManager->findDesiresByListOrderedByPriority($desireList);

            return $this->render('desire/list_own.html.twig', [
                'desires' => $desires,
                'list' => $desireList,
            ]);
        }

        $desires = [];
        return $this->render('desire/list_foreign.html.twig', [
            'desires' => $desires,
            'list' => $desireList,
        ]);
    }

    #[Route('/increase/{desireList}/{desire}', name: 'app_desire_increase_priority')]
    public function increasePriority(DesireList $desireList, Desire $desire): Response
    {

        $this->desireManager->increasePriority($desireList, $desire);

        return $this->redirect($this->generateUrl('app_desire_list', ['desireList' => $desireList->getId()]));
    }

    #[Route('/decrease/{desireList}/{desire}', name: 'app_desire_decrease_priority')]
    public function decreasePriority(DesireList $desireList, Desire $desire): Response
    {
        $this->desireManager->decreasePriority($desireList, $desire);

        return $this->redirect($this->generateUrl('app_desire_list', ['desireList' => $desireList->getId()]));
    }

    #[Route('/reserve/{desireList}/{desire}', name: 'app_desire_reserve')]
    public function reserveDesire(DesireList $desireList, Desire $desire): Response
    {

        $desireList->getAccessRoles();

        return $this->redirect($this->generateUrl('app_desire_list', ['desireList' => $desireList->getId()]));
    }

    #[Route('/create/{desireList}', name: 'app_desire_create')]
    public function create(Request $request, DesireList $desireList): Response
    {
        if ($desireList->getOwner() !== $this->getUser()){
            return $this->redirect($this->generateUrl('app_home', []));
        }

        $data = new DesireData();
        $form = $this->createForm(DesireCreateType::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DesireData $data */
            $data = $form->getData();
            $data->setOwner($this->getUser());

            $this->desireManager->storeDesire($data, $desireList, $this->getUser());
            return $this->redirect($this->generateUrl('app_desire_list', ['desireList' => $desireList->getId()]));
        }

        return $this->render('desire/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
