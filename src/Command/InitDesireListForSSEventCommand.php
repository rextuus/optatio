<?php

namespace App\Command;

use App\Content\Event\EventManager;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventJoinData;
use App\Content\SecretSanta\SecretSantaEvent\SecretSantaEventService;
use App\Content\SecretSanta\SecretSantaService;
use App\Content\User\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:init-desire-list-for-ss-event',
    description: 'Add a short description for your command',
)]
class InitDesireListForSSEventCommand extends Command
{
    // Inject the EventManager service
    public function __construct(
        private UserService $userService,
        private SecretSantaEventService $secretSantaEventService,
        private EventManager $eventManager
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('userId', InputArgument::REQUIRED, 'User ID')
            ->addArgument('secretSantaEventId', InputArgument::REQUIRED, 'SS ID')
            ->addArgument('firstRound', InputArgument::REQUIRED, 'First Round')
            ->addArgument('secondRound', InputArgument::REQUIRED, 'Second Round')
            ->setDescription('Add a user to the SecretSantaEvent');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = $input->getArgument('userId');
        $firstRound = $input->getArgument('firstRound');
        $secondRound = $input->getArgument('secondRound');

        $user = $this->userService->getUser($userId);
        $event = $this->secretSantaEventService->findBy(['id' => $input->getArgument('secretSantaEventId')])[0];

        $data = new SecretSantaEventJoinData();
        $data->setFirstRound($firstRound);
        $data->setSecondRound($secondRound);

        $io = new SymfonyStyle($input, $output);
        $confirmation = $io->ask('Do you want to add the user '.$user->getFullName().' to the SecretSantaEvent '.$event->getName().'? (y/n)');

        if (strtolower($confirmation) === 'y') {
            // Add user to the SecretSantaEvent using the EventManager service
//            $this->eventManager->addParticipantToSecretSantaEvent($user, $event, $data);

            $io->success('User added to SecretSantaEvent successfully!');
        } else {
            $io->warning('User was not added to SecretSantaEvent.');
        }

        // Add user to the SecretSantaEvent using the EventManager service
        $this->eventManager->fixDesireList($user, $event, $data);

        $io = new SymfonyStyle($input, $output);
        $io->success('User added to SecretSantaEvent successfully!');

        return Command::SUCCESS;
    }
}
