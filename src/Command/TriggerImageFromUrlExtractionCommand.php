<?php

namespace App\Command;

use App\Content\Desire\DesireService;
use App\Message\InitImageExtraction;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsCommand(
    name: 'app:extract-images-from-urls',
    description: 'Add a short description for your command',
)]
class TriggerImageFromUrlExtractionCommand extends Command
{
    public function __construct(
        private readonly DesireService $desireService,
        private readonly MessageBusInterface $messageBus,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $desires = $this->desireService->getDesiresForImageExtraction();

        $delay = 0;
        foreach ($desires as $desire) {
            $this->messageBus->dispatch(
                new InitImageExtraction($desire->getId()),
                [new DelayStamp($delay)]
            );
            $delay = $delay + 60000;
        }

        return Command::SUCCESS;
    }
}
