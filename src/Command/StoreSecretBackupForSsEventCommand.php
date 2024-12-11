<?php

namespace App\Command;

use App\Content\SecretSanta\SecretSantaEvent\SecretSantaEventService;
use App\Entity\Secret;
use App\Entity\SecretBackup;
use App\Repository\SecretBackupRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:store-secret-backup-for-ss-event',
    description: 'Add a short description for your command',
)]
class StoreSecretBackupForSsEventCommand extends Command
{
    public function __construct(
        private readonly SecretSantaEventService $secretSantaEventService,
        private readonly SecretBackupRepository $secretBackupRepository,
    ) {
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
        $io = new SymfonyStyle($input, $output);

        $event = $this->secretSantaEventService->findBy(['id' => $input->getArgument('secretSantaEventId')])[0];

        if ($event->getSecretBackups()->count() > 0) {
            $io->error('Secret Backup already exists!');
            $io->info("Secrets:");
            $io->info("\n".
                implode(
                    '',
                    array_map(function (Secret $secret) {
//                        return $secret->getProvider()->getId(). " -> ".$secret->getReceiver()->getId()."\n";
                        return $secret->getProvider()->getFullName(). " -> ?\n";
                    },
                        $event->getSecretBackups()->first()->getSecrets()->toArray()
                    )
                )
            );

            return Command::FAILURE;
        }

        $confirmation = $io->ask('Round 1 or 2?');
        $round = 1;
        $secrets = $event->getFirstRound()->getSecrets()->toArray();
        if (strtolower($confirmation) === '2') {
            $secrets = $event->getSecondRound()->getSecrets()->toArray();
            $round = 2;
        }

        $toBackup = [];
        foreach ($secrets as $secret) {
            $confirmation = $io->ask('Backup Secret for ' . $secret->getProvider()->getFullName() . '? (y/n)');
            if (strtolower($confirmation) === 'y') {
                $toBackup[] = $secret;
            }
        }

        $io->note(
            'Will store secrets for: ' .
            implode(
                ', ',
                array_map(function ($secret) {
                    return $secret->getProvider()->getFullName();
                }, $toBackup),
            )
        );
        $confirmation = $io->ask('Process? (y/n)');

        if (strtolower($confirmation) === 'y') {
            $secretBackup = new SecretBackup();
            $secretBackup->setSecretSantaEvent($event);
            $secretBackup->setRound(1);

            foreach ($toBackup as $secret) {
                $secret->setSecretBackup($secretBackup);
                $secretBackup->addSecret($secret);
            }

            $this->secretBackupRepository->save($secretBackup);
        }

        return Command::SUCCESS;
    }
}
