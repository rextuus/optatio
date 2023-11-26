<?php
declare(strict_types=1);

namespace App\Content\Desire\Url;

use App\Content\Desire\Url\Data\UrlData;
use App\Entity\Url;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class UrlFactory
{
    public function createByData(UrlData $data): Url
    {
        $url = $this->createNewInstance();
        $this->mapData($data, $url);
        return $url;
    }

    public function mapData(UrlData $data, Url $url): Url
    {
        $url->setDesire($data->getDesire());
        $url->setPath($data->getPath());

        return $url;
    }

    private function createNewInstance(): Url
    {
        return new Url();
    }
}
