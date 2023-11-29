<?php
declare(strict_types=1);

namespace App\Content\Desire;

use App\Content\Desire\Data\DesireData;
use App\Content\Desire\Url\Data\UrlData;
use App\Content\Desire\Url\UrlService;
use App\Entity\Desire;
use App\Entity\DesireList;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class DesireService
{
    public function __construct(private readonly DesireRepository $repository, private readonly DesireFactory $factory, private UrlService $urlService)
    {
    }

    public function createByData(DesireData $data): Desire
    {
        $desire = $this->factory->createByData($data);
        $this->repository->save($desire);

        if ($data->getUrl1()){
            $urlData = new UrlData();
            $urlData->setDesire($desire);
            $urlData->setPath($data->getUrl1());
            $this->urlService->createByData($urlData);
        }

        if ($data->getUrl2()){
            $urlData = new UrlData();
            $urlData->setDesire($desire);
            $urlData->setPath($data->getUrl2());
            $this->urlService->createByData($urlData);
        }

        if ($data->getUrl3()){
            $urlData = new UrlData();
            $urlData->setDesire($desire);
            $urlData->setPath($data->getUrl3());
            $this->urlService->createByData($urlData);
        }

        return $desire;
    }

    public function update(Desire $desire, DesireData $data): Desire
    {
        $desire = $this->factory->mapData($data, $desire);
        $this->repository->save($desire);

        if ($data->getUrl1()){
            $url = $desire->getUrls()->get(0);
            $urlData = (new UrlData())->initFromEntity($url);
            $urlData->setPath($data->getUrl1());
            $this->urlService->update($url, $urlData);
        }

        if ($data->getUrl2()){
            $url = $desire->getUrls()->get(1);
            $urlData = (new UrlData())->initFromEntity($url);
            $urlData->setPath($data->getUrl2());
            $this->urlService->update($url, $urlData);
        }

        if ($data->getUrl3()){
            $url = $desire->getUrls()->get(2);
            $urlData = (new UrlData())->initFromEntity($url);
            $urlData->setPath($data->getUrl3());
            $this->urlService->update($url, $urlData);
        }
        return $desire;
    }

    /**
     * @return Desire[]
     */
    public function findBy(array $conditions): array
    {
        return $this->repository->findBy($conditions, ['priority' => 'DESC']);
    }

    /**
     * @return Desire[]
     */
    public function findByListOrderedByPriority(DesireList $list, bool $isForeign = false): array
    {
        return $this->repository->findByListOrderByPriority($list, $isForeign);
    }
}
