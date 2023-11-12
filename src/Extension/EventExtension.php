<?php
declare(strict_types=1);

namespace App\Extension;

use App\Content\Event\EventService;
use App\Entity\Event;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class EventExtension extends AbstractExtension
{
    public function __construct(private Environment $environment, private Security $security)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_event', [$this, 'renderEvent']),
        ];
    }

    public function renderEvent(Event $event): string
    {
        $user = $this->security->getUser();

        return $this->environment->render(
            'extension/event/card.html.twig',
            [
                'user' => $user,
                'event' => $event,
            ]
        );
    }
}
