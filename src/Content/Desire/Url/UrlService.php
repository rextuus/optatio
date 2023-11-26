<?php
declare(strict_types=1);

namespace App\Content\Desire\Url;

use App\Content\Desire\Url\Data\UrlData;
use App\Entity\Url;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class UrlService
{
    public function __construct(private readonly UrlRepository $repository, private readonly UrlFactory $factory)
    {
    }

    public function createByData(UrlData $data): Url
    {
        $url = $this->factory->createByData($data);
        $this->repository->save($url);
        return $url;
    }

    public function update(Url $url, UrlData $data): Url
    {
        $url = $this->factory->mapData($data, $url);
        $this->repository->save($url);
        return $url;
    }

    /**
     * @return Url[]
     */
    public function findBy(array $conditions): array
    {
        return $this->repository->findBy($conditions);
    }
}
