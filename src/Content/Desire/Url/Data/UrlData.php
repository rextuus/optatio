<?php
declare(strict_types=1);

namespace App\Content\Desire\Url\Data;

use App\Entity\Desire;
use App\Entity\Url;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class UrlData
{
    private string $path;
    private Desire $desire;

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): UrlData
    {
        $this->path = $path;
        return $this;
    }

    public function getDesire(): Desire
    {
        return $this->desire;
    }

    public function setDesire(Desire $desire): UrlData
    {
        $this->desire = $desire;
        return $this;
    }

    public function initFromEntity(Url $url): UrlData
    {
        $this->path = $url->getPath();
        $this->desire = $url->getDesire();

        return $this;
    }
}
