<?php

declare(strict_types=1);

namespace App\Content\Image;

use App\Content\Image\Data\ImageCreateData;
use App\Content\Image\Data\ImageData;
use App\Entity\Image;
use DateTime;
use Symfony\Component\Uid\Factory\UuidFactory;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class ImageFactory
{
    public function __construct(private UuidFactory $uuidFactory) { }

    public function create(): Image
    {
        $image = $this->getNewInstance();

        $uuid = $this->uuidFactory->create();
        $image->setName($uuid->toBase58());

        return $image;
    }

    public function mapData(Image $image, ImageData $data): void
    {
        if ($data instanceof ImageCreateData) {
            $image->setCreated(new DateTime());
        }

        $image->setOwner($data->getOwner());
        $image->setUploaded($data->getUploaded());
        $image->setDelivered($data->getDelivered());
        $image->setDisplayed($data->getDisplayed());
        $image->setFilePath($data->getFilePath());
        $image->setCdnUrl($data->getCdnUrl());
    }

    private function getNewInstance(): Image
    {
        return new Image();
    }
}
