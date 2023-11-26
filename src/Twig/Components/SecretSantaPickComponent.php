<?php

namespace App\Twig\Components;

use App\Content\User\UserService;
use App\Entity\DesireList;
use App\Entity\Secret;
use App\Entity\SecretSantaEvent;
use App\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent()]
final class SecretSantaPickComponent
{
    use DefaultActionTrait;

    public Secret $secret;
    public SecretSantaEvent $secretSantaEvent;
    public User $currentUser;

    public bool $firstRound;

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getSecretName(): string
    {
        if ($this->secret->getReceiver()){
            return $this->secret->getReceiver()->getFirstName();

        }
        return '';
    }

    public function getBackUrl(): string
    {
        return $this->urlGenerator->generate(
            'app_secret_santa_detail',
            ['event' => $this->secretSantaEvent->getId()]
        );
    }

    public function getSecretUrl(): string
    {
        if ($this->firstRound){
            return $this->urlGenerator->generate(
                'app_secret_santa_pick_first',
                [
                    'event' => $this->secretSantaEvent->getId(),
                    'user' => $this->currentUser->getId()
                ]
            );
        }
        return $this->urlGenerator->generate(
            'app_secret_santa_pick_second',
            [
                'event' => $this->secretSantaEvent->getId(),
                'user' => $this->currentUser->getId()
            ]
        );

    }
}
