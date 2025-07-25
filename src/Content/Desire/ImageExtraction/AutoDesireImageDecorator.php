<?php

declare(strict_types=1);

namespace App\Content\Desire\ImageExtraction;

use App\Entity\Desire;
use App\Entity\ExtractedDesireImageCollection;
use Symfony\Component\Uid\Uuid;

class AutoDesireImageDecorator
{
    public function __construct(
        private readonly ExtractPicsApiService $extractPicsApiService,
        private readonly ExtractedDesireImageCollectionRepository $repository
    ) {
    }

    public function decorateWithImage(Desire $desire): ?ExtractedDesireImageCollection
    {
        // Check if the desire has any URLs
        if ($desire->getUrls()->isEmpty()) {
            return null;
        }

        // Get the first URL
        $url = $desire->getUrls()->first();
        if ($url === null) {
            return null;
        }

        $urlPath = $url->getPath();
        if (empty($urlPath)) {
            return null;
        }

        // Start the extraction process
        $extractionResult = $this->extractPicsApiService->startExtraction($urlPath);

        // Create a new ExtractedDesireImageCollection entity
        $collection = new ExtractedDesireImageCollection();
        $collection->setDesire($desire);
        $collection->setUrl($urlPath);
        $collection->setExtractionId($extractionResult->getId() ?: Uuid::v4()->toRfc4122());
        $collection->setProjectId($extractionResult->getProjectId());
        $collection->setStatus(PicsExtractionState::PENDING);

        // Save the entity
        $this->repository->save($collection);

        // Add the collection to the desire
        $desire->addExtractedDesireImageCollection($collection);

        return $collection;
    }
}
