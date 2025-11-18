<?php

namespace App\Controller;

use App\Content\Desire\Data\DesireData;
use App\Content\Desire\DesireManager;
use App\Content\Desire\DesireService;
use App\Content\Desire\ImageExtraction\ExtractedDesireImageCollectionRepository;
use App\Content\Desire\ImageExtraction\PicsExtractionState;
use App\Content\Image\Data\ImageCreateData;
use App\Content\Image\Data\ImageData;
use App\Content\Image\ImageService;
use App\Content\DesireList\Data\DesireCopyData;
use App\Content\DesireList\DesireListService;
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
        private readonly DesireService $desireService,
        private readonly DesireListService $desireListService,
        private readonly ExtractedDesireImageCollectionRepository $extractedDesireImageCollectionRepository,
        private readonly ImageService $imageService,
    )
    {
    }

    #[Route('/list/{desireList}', name: 'app_desire_list')]
    public function index(DesireList $desireList): Response
    {
        $user = $this->getLoggedInUser();
        $check = $this->checkDesireListAccess($user, $desireList);
        if ($check !== null){
            return $check;
        }

        if ($desireList->isMaster()){
            return $this->redirect($this->generateUrl('app_desire_home', ['from' => $desireList->getId()]));
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
            $data->setExclusive(!$data->isExclusive());
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
        $data->setExclusive(!$desire->isExclusive());

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
            $data->setExclusive(!$data->isExclusive());

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

    #[Route('/master', name: 'app_desire_home')]
    public function master(Request $request): Response
    {
        $user = $this->getLoggedInUser();

        $defaultList = $this->desireManager->getMasterListByUser($user);

        return $this->render('desire/home.html.twig', [
            'masterList' => $defaultList,
        ]);
    }

    #[Route('/switch', name: 'app_desire_switch')]
    public function switch(Request $request): Response
    {
        $fromDesireListId = $request->get('from');
        $desireId = $request->get('desire');
        $user = $this->getLoggedInUser();

        $defaultList = $this->desireManager->getMasterListByUser($user);
        if ($fromDesireListId){
            $list = $this->desireListService->findBy(['id' => $fromDesireListId]);
            if ($list !== []){
                $defaultList = $list[0];
            }
        }

        $desires = [];
        if ($desireId){
            $desires = $this->desireService->findBy(['id' => $desireId]);
        }

        $otherLists = $this->desireManager->getNonMasterListsByUser($user);

        $showForm = false;
        $data = new DesireCopyData();
        $data->setFrom($defaultList);
        if (array_key_exists(0, $otherLists)){
            $data->setTo($otherLists[0]);
            $showForm = true;
        }
        $data->setDesires($desires);

        return $this->render('desire/switch.html.twig', [
            'showForm' => $showForm,
            'initialFormData' => $data,
            'user' => $user,
            'masterList' => $defaultList,
        ]);
    }

    private function checkDesireListAccess(User $user, DesireList $desireList): ?Response
    {
        $event = $desireList->getEvents()->first();
        $hasAccess = $this->accessRoleService->checkDesireListAccess($user, $desireList, $event);

        if (!$hasAccess && $event->getEventType() === EventType::SECRET_SANTA){
            if ($desireList->getEvents()->containsKey(1)){
                $event = $desireList->getEvents()->get(1);
                $hasAccess = $this->accessRoleService->checkDesireListAccess($user, $desireList, $event);
            }
        }
//dd();
        if ($hasAccess){
            return null;
        }

        return $this->redirect($this->generateUrl('app_event_list'));
    }

    #[Route('/extracted-images/{desireList}/{desire}', name: 'app_extracted_desire_images')]
    public function viewExtractedImages(DesireList $desireList, Desire $desire): Response
    {
        $user = $this->getLoggedInUser();
        if ($desire->getOwner() !== $user) {
            return $this->redirect($this->generateUrl('app_event_list', []));
        }

        // Get all extracted image collections for this desire
        $collections = $this->extractedDesireImageCollectionRepository->findByDesire($desire->getId());

        // Filter to only include completed collections
        $completedCollections = array_filter($collections, function($collection) {
            return $collection->getStatus() === PicsExtractionState::DONE;
        });

        return $this->render('desire/extracted_images.html.twig', [
            'desire' => $desire,
            'desireList' => $desireList,
            'collections' => $completedCollections,
        ]);
    }

    #[Route('/select-extracted-image/{desireList}/{desire}/{imageUrl}', name: 'app_desire_select_extracted_image', requirements: ['imageUrl' => '.+'])]
    public function selectExtractedImage(DesireList $desireList, Desire $desire, string $imageUrl): Response
    {
        $user = $this->getLoggedInUser();
        if ($desire->getOwner() !== $user) {
            return $this->redirect($this->generateUrl('app_event_list', []));
        }

        // Create a new image entity
        $imageData = new ImageCreateData();
        $imageData->setOwner($user);
        $imageData->setDesire($desire);
        $imageData->setFilePath($imageUrl);
        $imageData->setCdnUrl($imageUrl);

        // Create the image
        $this->imageService->createByData($imageData);

        // Redirect back to the desire list
        $this->addFlash('success', 'Das Bild wurde erfolgreich als Hauptbild für deinen Wunsch ausgewählt.');
        return $this->redirect($this->generateUrl('app_desire_list', ['desireList' => $desireList->getId()]));
    }
}
