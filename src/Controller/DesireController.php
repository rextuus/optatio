<?php

namespace App\Controller;

use App\Content\Desire\Data\DesireData;
use App\Content\Desire\DesireManager;
use App\Content\DesireList\Data\DesireCopyData;
use App\Content\Event\EventType;
use App\Content\SecretSanta\SecretSantaEvent\SecretSantaEventService;
use App\Content\SecretSanta\SecretSantaService;
use App\Content\User\AccessRoleService;
use App\Entity\AccessRole;
use App\Entity\Desire;
use App\Entity\DesireList;
use App\Entity\Event;
use App\Entity\Image;
use App\Entity\User;
use App\Form\DesireCopyType;
use App\Form\DesireCreateType;
use App\Form\DesireEditType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/desire')]
#[IsGranted('ROLE_USER')]
class DesireController extends BaseController
{


    public function __construct(
        private readonly DesireManager $desireManager,
        private readonly SecretSantaEventService $secretSantaService,
        private readonly AccessRoleService $accessRoleService,
    )
    {
    }

    #[Route('/list/{desireList}', name: 'app_desire_list')]
    public function index(DesireList $desireList): Response
    {
        $user = $this->getLoggedInUser();
        $check = $this->checkDesireListAccess($user, $desireList);
        if ($check){
//            return $check;
        }

        // get first event for redirect => TODO we need a param to know where we come from ugly bugly fucking
        $event = $desireList->getEvents()->first();
        $ssEvents = $this->secretSantaService->findByFirstOrSecondRound($event);

        $ssEvent = null;
        if (count($ssEvents) > 0){
            $ssEvent = $ssEvents[0];
        }

        // own list
        $desires = $this->desireManager->findDesiresByListOrderedByPriority($desireList);
        if ($desireList->getOwner() === $user){
            return $this->render('desire/list_own.html.twig', [
                'desires' => $desires,
                'list' => $desireList,
                'ssEvent' => $ssEvent,
                'event' => $event,
            ]);
        }

        // foreign list
        $desires = $this->desireManager->findDesiresByListOrderedByPriority($desireList, true);
        return $this->render('desire/list_foreign.html.twig', [
            'desires' => $desires,
            'list' => $desireList,
            'currentUser' => $user,
            'ssEvent' => $ssEvent,
            'event' => $event,
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
        $user = $this->getLoggedInUser();
        $check = $this->checkDesireListAccess($user, $desireList);
        if ($check){
//            return $check;
        }

        $this->desireManager->addReservation($user, $desire);

        return $this->redirect($this->generateUrl('app_desire_list', ['desireList' => $desireList->getId()]));
    }

    #[Route('/release/{desireList}/{desire}', name: 'app_desire_release')]
    public function releaseDesire(DesireList $desireList, Desire $desire): Response
    {
        $user = $this->getLoggedInUser();
        $check = $this->checkDesireListAccess($user, $desireList);
        if ($check) {
//            return $check;
        }

        $this->desireManager->removeReservation($user, $desire);

        return $this->redirect($this->generateUrl('app_desire_list', ['desireList' => $desireList->getId()]));
    }

    #[Route('/resolve/{desireList}/{desire}', name: 'app_desire_resolve')]
    public function resolveDesire(DesireList $desireList, Desire $desire): Response
    {
        $user = $this->getLoggedInUser();
        $check = $this->checkDesireListAccess($user, $desireList);
        if ($check) {
//            return $check;
        }

        $this->desireManager->resolveReservation($user, $desire);

        return $this->redirect($this->generateUrl('app_desire_list', ['desireList' => $desireList->getId()]));
    }

    #[Route('/create/{desireList}', name: 'app_desire_create')]
    public function create(Request $request, DesireList $desireList): Response
    {
        if ($desireList->getOwner() !== $this->getUser()){
            return $this->redirect($this->generateUrl('app_event_list', []));
        }

        $data = new DesireData();
        $form = $this->createForm(DesireCreateType::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DesireData $data */
            $data = $form->getData();
            $data->setOwner($this->getUser());

            $this->desireManager->storeDesire($data, $desireList);
            return $this->redirect($this->generateUrl('app_desire_list', ['desireList' => $desireList->getId()]));
        }

        return $this->render('desire/create.html.twig', [
            'form' => $form->createView(),
            'desireList' => $desireList,
        ]);
    }

    #[Route('/edit/{desireList}/{desire}', name: 'app_desire_edit')]
    public function edit(Request $request, DesireList $desireList, Desire $desire): Response
    {
        $user = $this->getLoggedInUser();
        if ($desireList->getOwner() !== $user || $desire->getOwner() !== $user){
            return $this->redirect($this->generateUrl('app_event_list', []));
        }

        $data = (new DesireData())->initFromEntity($desire);

        $urls = $desire->getUrls();
        if ($urls->get(0)){
            $data->setUrl1($urls->get(0)->getPath());
        }
        if ($urls->get(1)){
            $data->setUrl2($urls->get(1)->getPath());
        }
        if ($urls->get(2)){
            $data->setUrl3($urls->get(2)->getPath());
        }


        $form = $this->createForm(DesireEditType::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DesireData $data */
            $data = $form->getData();

            $this->desireManager->updateDesire($data, $desire);
            return $this->redirect($this->generateUrl('app_desire_list', ['desireList' => $desireList->getId()]));
        }

        return $this->render('desire/edit.html.twig', [
            'form' => $form->createView(),
            'desireList' => $desireList,
        ]);
    }

    #[Route('/delete/{desire}/{image}', name: 'app_desire_image_delete')]
    public function delete(Request $request, Desire $desire, Image $image): Response
    {
        $user = $this->getLoggedInUser();
        if ($desire->getOwner() !== $user || $image->getOwner() !== $user){
            return $this->redirect($this->generateUrl('app_event_list', []));
        }

        $this->desireManager->deleteImageOfDesire($desire, $image);

        return $this->json([true]);
    }

    #[Route('/home', name: 'app_desire_home')]
    public function master(Request $request): Response
    {
        $user = $this->getLoggedInUser();

        $masterList = $this->desireManager->getMasterListByUser($user);
        $otherLists = $this->desireManager->getNonMasterListsByUser($user);

        $data = new DesireCopyData();
        $data->setFrom($masterList);
        $data->setTo($otherLists[0]);
        $data->setDesires([]);

        return $this->render('desire/home.html.twig', [
            'initialFormData' => $data,
            'user' => $user,
        ]);
    }

    private function checkDesireListAccess(User $user, DesireList $desireList): ?Response
    {
        if ($this->accessRoleService->checkDesireListAccess($user, $desireList)){
            return null;
        }
        return $this->redirect($this->generateUrl('app_event_list'));
    }
}
