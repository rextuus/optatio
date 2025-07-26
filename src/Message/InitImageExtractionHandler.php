<?php

namespace App\Message;

use App\Content\Desire\DesireRepository;
use App\Content\Desire\DesireService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class InitImageExtractionHandler
{
    public function __construct(
        private readonly DesireRepository $repository,
        private readonly DesireService $desireService,
    ) {
    }

    public function __invoke(InitImageExtraction $message): void
    {
        $desire = $this->repository->find($message->getDesireId());
        $this->desireService->initImageFromUrlExtraction($desire);
    }
}
