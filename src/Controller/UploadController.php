<?php

namespace App\Controller;

use App\Content\Image\Data\ImageCreateData;
use App\Content\Image\ImageService;
use App\Entity\Desire;
use App\Entity\DesireList;
use App\Form\ImageUploadType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class UploadController extends BaseController
{
    #[Route('/desire/image/upload/{desire}/{desireList}', name: 'app_upload_desire_image')]
    public function upload(Desire $desire, DesireList $desireList, Request $request, ImageService $imageService): Response
    {
        $form = $this->createForm(ImageUploadType::class,);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('imageFile')->getData();

            $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/images';
            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move($uploadsDirectory, $filename);

            $imageData = new ImageCreateData();
            $imageData->setOwner($this->getLoggedInUser());
            $imageData->setDesire($desire);

            $imageData->setFilePath('uploads/images/' . $filename);

            $imageService->createByData($imageData);

            return $this->redirect($this->generateUrl('app_desire_list', ['desireList' => $desireList->getId()]));
        }

        return $this->render('desire/upload.html.twig', [
            'form' => $form->createView(),
            'desire' => $desire,
            'desireList' => $desireList,
        ]);
    }
}
