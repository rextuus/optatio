<?php

namespace App\Command;

use App\Content\Desire\DesireService;
use App\Content\SecretSanta\SecretSantaEvent\SecretSantaEventService;
use App\Content\User\AccessRoleService;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

#[AsCommand(
    name: 'app:extract-images-from-urls',
    description: 'Add a short description for your command',
)]
class TriggerImageFromUrlExtractionCommand extends Command
{
    public function __construct(
        private readonly DesireService $desireService,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $desires = $this->desireService->getDesiresForImageExtraction();

        foreach ($desires as $desire) {
            $this->desireService->initImageFromUrlExtraction($desire);
        }

        return Command::SUCCESS;
    }
}
