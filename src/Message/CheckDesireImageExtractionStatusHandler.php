<?php

declare(strict_types=1);

namespace App\Message;

use App\Content\Desire\ImageExtraction\ExtractedDesireImageCollectionRepository;
use App\Content\Desire\ImageExtraction\ExtractPicsApiService;
use App\Content\Desire\ImageExtraction\PicsExtractionState;
use App\Entity\Desire;
use App\Entity\Image;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
class CheckDesireImageExtractionStatusHandler
{
    public function __construct(
        private readonly ExtractedDesireImageCollectionRepository $repository,
        private readonly ExtractPicsApiService $extractPicsApiService,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(CheckDesireImageExtractionStatus $message): void
    {
        $collectionId = $message->getExtractedDesireImageCollectionId();
        $collection = $this->repository->find($collectionId);

        if ($collection === null) {
            $this->logger->error("ExtractedDesireImageCollection not found with ID: {$collectionId}");
            return;
        }

        try {
            $extractionResult = $this->extractPicsApiService->checkExtractionStatus($collection->getExtractionId());

            // Check if the extraction is complete
            if ($extractionResult->isComplete()) {
                // Update the collection status
                $collection->setStatus(PicsExtractionState::DONE);
                $collection->setImages($extractionResult->getImages());
                $this->repository->save($collection);

                // Get the first JPG image URL if available
                $imageUrl = $extractionResult->getFirstJpgImageUrl();
                if ($imageUrl !== null) {
                    // Get the desire from the collection
                    $desire = $collection->getDesire();
                    if ($desire !== null) {
                        // Create a new Image entity and associate it with the desire
                        $this->addImageToDesire($desire, $imageUrl);
                        $this->logger->info("Added image from extraction to Desire with ID: {$desire->getId()}");
                    }
                }

                return;
            }

            // If not complete, dispatch another check message with a delay
            $this->messageBus->dispatch(
                new CheckDesireImageExtractionStatus($collectionId),
                [new DelayStamp(30000)]
            );

            $this->logger->info(
                "Extraction still in progress for collection ID: {$collectionId}, checking again in 30 seconds"
            );
        } catch (\Exception $e) {
            $this->logger->error("Error checking extraction status: {$e->getMessage()}");

            // Retry in case of error
            $this->messageBus->dispatch(
                new CheckDesireImageExtractionStatus($collectionId),
                [new DelayStamp(30000)] // 30 seconds in milliseconds
            );
        }
    }

    private function addImageToDesire(Desire $desire, string $imageUrl): void
    {
        // Create a new Image entity
        $image = new Image();
        $image->setCdnUrl($imageUrl);
        $image->setFilePath($imageUrl);
        $image->setName('Extracted from URL: ' . substr($imageUrl, 0, 50) . '...');
        $image->setCreated(new DateTime());

        // Set the owner to the owner of the desire
        $image->setOwner($desire->getOwner());

        // Associate the image with the desire
        $desire->addImage($image);
        $image->setDesire($desire);

        // Persist the changes
        $this->entityManager->persist($image);
        $this->entityManager->persist($desire);
        $this->entityManager->flush();
    }
}
