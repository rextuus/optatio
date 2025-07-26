<?php
declare(strict_types=1);

namespace App\Content\Desire;

use App\Content\Desire\Data\DesireData;
use App\Content\Desire\Url\Data\UrlData;
use App\Content\Desire\Url\UrlService;
use App\Entity\Desire;
use App\Entity\DesireList;
use App\Entity\Image;
use App\Entity\SecretSantaEvent;
use App\Message\DesireImageExtraction;
use Symfony\Component\Messenger\MessageBusInterface;


class DesireService
{
    public function __construct(
        private readonly DesireRepository $repository, 
        private readonly DesireFactory $factory, 
        private readonly UrlService $urlService,
        private readonly MessageBusInterface $messageBus
    ) {
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

        // Dispatch a message to trigger image extraction if URLs were added
        if ($data->getUrl1() || $data->getUrl2() || $data->getUrl3()) {
            $this->initImageFromUrlExtraction($desire);
        }

        return $desire;
    }

    public function update(Desire $desire, DesireData $data): Desire
    {
        $desire = $this->factory->mapData($data, $desire);
        $this->repository->save($desire);

        $urlsUpdated = false;

        if ($data->getUrl1()) {
            $urlsUpdated = true;
            if ($desire->getUrls()->get(0)) {
                $url = $desire->getUrls()->get(0);
                $urlData = (new UrlData())->initFromEntity($url);
                $urlData->setPath($data->getUrl1());
                $this->urlService->update($url, $urlData);
            } else {
                $urlData = new UrlData();
                $urlData->setDesire($desire);
                $urlData->setPath($data->getUrl1());
                $this->urlService->createByData($urlData);
            }
        }

        if ($data->getUrl2()) {
            $urlsUpdated = true;
            if ($desire->getUrls()->get(1)) {
                $url = $desire->getUrls()->get(1);
                $urlData = (new UrlData())->initFromEntity($url);
                $urlData->setPath($data->getUrl2());
                $this->urlService->update($url, $urlData);
            } else {
                $urlData = new UrlData();
                $urlData->setDesire($desire);
                $urlData->setPath($data->getUrl2());
                $this->urlService->createByData($urlData);
            }
        }

        if ($data->getUrl3()) {
            $urlsUpdated = true;
            if ($desire->getUrls()->get(2)) {
                $url = $desire->getUrls()->get(2);
                $urlData = (new UrlData())->initFromEntity($url);
                $urlData->setPath($data->getUrl3());
                $this->urlService->update($url, $urlData);
            } else {
                $urlData = new UrlData();
                $urlData->setDesire($desire);
                $urlData->setPath($data->getUrl3());
                $this->urlService->createByData($urlData);
            }
        }

        // Dispatch a message to trigger image extraction if URLs were updated
        if ($urlsUpdated) {
            $this->initImageFromUrlExtraction($desire);
        }

        return $desire;
    }

    public function initImageFromUrlExtraction(Desire $desire): void
    {
        $this->messageBus->dispatch(new DesireImageExtraction($desire->getId()));
    }

    /**
     * @return Desire[]
     */
    public function findBy(array $conditions): array
    {
        return $this->repository->findBy($conditions);
    }

    /**
     * @return Desire[]
     */
    public function findByListOrderedByPriority(DesireList $list, bool $isForeign = false): array
    {
        return $this->repository->findByListOrderByPriority($list, $isForeign);
    }

    public function removeImage(Desire $desire, Image $image)
    {
        $desire->removeImage($image);
        $this->repository->save($desire);
    }

    public function getAllDesiresForSecretSantaEvent(SecretSantaEvent $event, bool $firstRound = true)
    {
        return $this->repository->getAllDesiresForSecretSantaEvent($event, $firstRound);
    }

    /**
     * @return array<Desire>
     */
    public function getDesiresForImageExtraction(): array
    {
        return $this->repository->findDesiresWithUrlsAndNoExtractedImages();
    }
}
