<?php

namespace App\Command;

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
    name: 'app:send-test-mail',
    description: 'Add a short description for your command',
)]
class SendTestMailCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('mail', InputArgument::REQUIRED, 'mail')
            ->setDescription('Add a user to the SecretSantaEvent');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mailAddress = $input->getArgument('mail');

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $mailAddress]);;

        $email = (new TemplatedEmail())
            ->from(new Address('debes@wh-company.de', 'WH-Company'))
            ->to($mailAddress)
            ->subject('Mail-Testing')
            ->htmlTemplate('mail/test.html.twig')
            ->context([
            ])
        ;

        $this->mailer->send($email);

        return Command::SUCCESS;
    }
}
