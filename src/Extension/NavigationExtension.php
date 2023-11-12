<?php
declare(strict_types=1);

namespace App\Extension;

use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


class NavigationExtension extends AbstractExtension
{
    public function __construct(private Environment $environment, private Security $security)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('navi_button', [$this, 'renderNaviButton']),
        ];
    }

    public function renderNaviButton(): string
    {
        $user = $this->security->getUser();

        return $this->environment->render(
            'extension/navi_button.html.twig',
            [
                'user' => $user
            ]
        );
    }
}