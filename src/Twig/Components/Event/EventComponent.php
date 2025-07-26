<?php

declare(strict_types=1);

namespace App\Twig\Components\Event;

use App\Content\Bookmark\BookmarkService;
use App\Content\Event\EventManager;
use App\Entity\Event;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent()]
class EventComponent extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?User $user = null;
    #[LiveProp]
    public ?Event $event = null;

    public function __construct(private readonly BookmarkService $bookmarkService)
    {
    }

    #[LiveAction]
    public function toggleBookmark(): RedirectResponse
    {
        $bookmark = $this->bookmarkService->userHasBookmarkForEvent($this->user, $this->event);
        if ($bookmark !== null){
            $this->bookmarkService->deleteBookmark($bookmark);

            return $this->redirect($this->generateUrl('app_home'));
        }

        $this->bookmarkService->createBookmark($this->user, $this->event, null);

        return $this->redirect($this->generateUrl('app_home'));
    }

    public function isUserParticipant(): bool
    {
        return in_array($this->user, $this->event->getParticipants()->toArray());
    }

    public function joinDisabled(): string
    {
        if ($this->isUserParticipant()) {
            return 'disabled';
        }
        return '';
    }

    public function exitDisabled(): string
    {
        if (!$this->isUserParticipant()) {
            return 'disabled';
        }
        return '';
    }


    public function canEdit(): bool
    {
        return in_array(
            EventManager::getEventOwnerRole($this->event),
            $this->user->getAccessRoles()->toArray()
        );
    }

    public function getHeaderText(): string
    {
        if ($this->canEdit()) {
            return 'Dies ist dein Event';
        }

        if ($this->isUserParticipant()) {
            return 'Du nimmst bereits teil';
        }

        return 'Das ist ein Event von ' . $this->event->getCreator()->getFirstName();
    }

    public function getBookmarkStateClass(): string
    {
        if ($this->bookmarkService->userHasBookmarkForEvent($this->user, $this->event)){
            return 'text-success';
        }

        return 'text-dark';
    }
}
