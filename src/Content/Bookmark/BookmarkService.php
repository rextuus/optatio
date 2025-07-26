<?php

declare(strict_types=1);

namespace App\Content\Bookmark;

use App\Entity\Event;
use App\Entity\EventBookmark;
use App\Entity\SecretSantaEvent;
use App\Entity\User;

class BookmarkService
{
    public function __construct(
        private readonly EventBookmarkRepository $bookmarkRepository,
    ) {
    }

    public function createBookmark(User $user, ?Event $event, ?SecretSantaEvent $secretSantaEvent): EventBookmark
    {
        $bookmark = new EventBookmark();
        $bookmark->setOwner($user);
        $bookmark->setEvent($event);
        $bookmark->setSecreSantaEvent($secretSantaEvent);

        $this->bookmarkRepository->save($bookmark);

        return $bookmark;
    }

    public function deleteBookmark(EventBookmark $bookmark): void
    {
        $this->bookmarkRepository->delete($bookmark);
    }

    /**
     * @return array<EventBookmark>
     */
    public function getBookmarksForUser(User $user): array
    {
        return $this->bookmarkRepository->findBy(['user' => $user]);
    }
}
