<?php

namespace App\Controller;

use App\Entity\Desire;
use App\Entity\User;
use App\Form\ImageUploadType;
use App\Content\Image\Data\ImageCreateData;
use App\Content\Image\ImageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class UploadController extends BaseController
{
    #[Route('/desire/image/upload/{desire}', name: 'app_upload')]
    public function upload(Desire $desire, Request $request, ImageService $imageService): Response
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
        }

        return $this->render('desire/upload.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
