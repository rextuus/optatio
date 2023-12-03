<?php

declare(strict_types=1);

namespace App\Message;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class ImageUpload
{
    public function __construct(
        private int $imageId,
    ) {
    }

    public function getImageId(): int
    {
        return $this->imageId;
    }
}