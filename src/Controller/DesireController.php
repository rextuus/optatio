<?php

namespace App\Controller;

use App\Content\Desire\Data\DesireData;
use App\Content\Desire\DesireManager;
use App\Entity\AccessRole;
use App\Entity\Desire;
use App\Entity\DesireList;
use App\Entity\User;
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
        private readonly DesireManager $desireManager
    )
    {
    }

    #[Route('/list/{desireList}', name: 'app_desire_list')]
    public function index(DesireList $desireList): Response
    {
        $user = $this->getLoggedInUser();
        $check = $this->checkDesireListAccess($user, $desireList);
        if ($check){
            return $check;
        }

        $desires = $this->desireManager->findDesiresByListOrderedByPriority($desireList);
        if ($desireList->getOwner() === $user){
            return $this->render('desire/list_own.html.twig', [
                'desires' => $desires,
                'list' => $desireList,
            ]);
        }

        $desires = $this->desireManager->findDesiresByListOrderedByPriority($desireList, true);
        return $this->render('desire/list_foreign.html.twig', [
            'desires' => $desires,
            'list' => $desireList,
            'currentUser' => $user,
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
            return $check;
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
            return $check;
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
            return $check;
        }

        $this->desireManager->resolveReservation($user, $desire);

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


    private function checkDesireListAccess(User $user, DesireList $desireList): ?Response
    {
        $desireListIdents = $desireList->getAccessRoles()->map(
            function (AccessRole $accessRole) {
                return $accessRole->getIdent();
            }
        )->toArray();
        $userIdents = $user->getAccessRoles()->map(
            function (AccessRole $accessRole) {
                return $accessRole->getIdent();
            }
        )->toArray();

        // check if its own list
        if (in_array('USER_'.$user->getId(), $desireListIdents)){
            return null;
        }

        // check if user has access via secret
        $secretIdent = 'secretIdent';
        $ownerId = -1;
        foreach ($desireListIdents as $ident){
            preg_match('~^ROLE_SECRET_FOR_USER_(\d*)?~', $ident, $matches);
            if ($matches){
                $secretIdent = $matches[0];
                $ownerId = $matches[1];
            }
        }

        $userId = -2;
        foreach ($userIdents as $ident){
            preg_match('~^USER_(\d*)?~', $ident, $matches);
            if ($matches){
                $userId = $matches[1];
            }
        }

        $userIsOwner = $ownerId === $userId;
        $userIsSecret = in_array($secretIdent, $userIdents);

        $shared = array_intersect($desireListIdents, $userIdents);
        if (count($shared) && ($userIsOwner || $userIsSecret) > 0){
            return null;
        }
        return $this->redirect($this->generateUrl('app_home', []));
    }
}
