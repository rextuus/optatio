<?php

namespace App\Command;

use App\Content\SecretSanta\SecretSantaEvent\SecretSantaEventService;
use App\Content\User\AccessRoleService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:allow-access-for-everyone-to-everyone',
    description: 'Add a short description for your command',
)]
class AllowAccessForEveryoneToEveryoneCommand extends Command
{
    public function __construct(
        private SecretSantaEventService $secretSantaEventService,
        private AccessRoleService $accessRoleService,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('secretSantaEventId', InputArgument::REQUIRED, 'SS ID')
            ->setDescription('Add a user to the SecretSantaEvent');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ssEvent = $this->secretSantaEventService->findBy(['id' => $input->getArgument('secretSantaEventId')])[0];

        $event = $ssEvent->getFirstRound();
        $participants = $event->getParticipants();
        foreach ($participants as $participant) {
            foreach ($participants as $participant2) {
                $this->accessRoleService->addSecretRoleToProvider($participant, $participant2, $event);
            }
        }

        $event = $ssEvent->getSecondRound();

        if ($event !== null) {
            $participants = $event->getParticipants();
            foreach ($participants as $participant) {
                foreach ($participants as $participant2) {
                    if ($participant !== $participant2) {
                        $this->accessRoleService->addSecretRoleToProvider($participant, $participant2, $event);
                    }
                }
            }
        }

        return Command::SUCCESS;
    }
}
