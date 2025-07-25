<?php

declare(strict_types=1);

namespace App\Message;

use App\Content\Desire\DesireRepository;
use App\Content\Desire\ImageExtraction\AutoDesireImageDecorator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
class DesireImageExtractionHandler
{
    public function __construct(
        private readonly DesireRepository $desireRepository,
        private readonly AutoDesireImageDecorator $autoDesireImageDecorator,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(DesireImageExtraction $message): void
    {
        $desireId = $message->getDesireId();
        $desire = $this->desireRepository->find($desireId);

        if ($desire === null) {
            return;
        }

        $collection = $this->autoDesireImageDecorator->decorateWithImage($desire);

        if ($collection === null) {
            return;
        }

        $this->messageBus->dispatch(
            new CheckDesireImageExtractionStatus($collection->getId()),
            [new DelayStamp(30000)]
        );
    }
}
