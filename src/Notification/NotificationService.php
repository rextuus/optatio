<?php

declare(strict_types=1);

namespace App\Notification;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use App\Entity\User;

class NotificationService
{
    public function __construct(
        private MailerInterface $mailer,
    ) {}

    public function sendWishlistWishAddedNotification(User $receiver, string $eventName, string $wishTitle, string $creator): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@optatio.local', 'Optatio Bot'))
            ->to($receiver->getEmail())
            ->subject(sprintf('✉️ Neuer Wunsch in %s!', $eventName))
            ->htmlTemplate('emails/wish_added.html.twig')
            ->context([
                'user' => $receiver,
                'event_name' => $eventName,
                'wish_title' => $wishTitle,
                'creator' => $creator,
            ]);

        $this->mailer->send($email);
    }
}
