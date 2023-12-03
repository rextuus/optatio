<?php

declare(strict_types=1);

namespace App\Message;

use App\Content\Image\CloudinaryApiGateway;
use App\Content\Image\Data\ImageData;
use App\Content\Image\ImageService;
use DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
#[AsMessageHandler]
class ImageUploadHandler
{
    public function __construct(
        private CloudinaryApiGateway $cloudinaryApiGateway,
        private ImageService $imageService,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ImageUpload $message): void
    {
        $image = $this->imageService->find($message->getImageId());

        $result = $this->cloudinaryApiGateway->uploadImage($image);

        if ($result) {
            $infos = $result->getArrayCopy();
            $url = $infos['secure_url'];

            $updateData = (new ImageData())->initFrom($image);
            $updateData->setCdnUrl($url);
            $updateData->setUploaded(new DateTime());
            $this->imageService->update($image, $updateData);

            $this->logger->info("Upload finished");
            $this->logger->info($image->getFilePath() . ': ' . $url);
            return;
        }
        $this->logger->error("Upload failed");
    }
}