<?php
declare(strict_types=1);

namespace App\Content\DesireList;

use App\Content\DesireList\Data\DesireListData;
use App\Entity\DesireList;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class DesireListFactory
{
    public function createByData(DesireListData $data): DesireList
    {
        $desireList = $this->createNewInstance();
        $this->mapData($data, $desireList);
        return $desireList;
    }

    public function mapData(DesireListData $data, DesireList $desireList): DesireList
    {
        $desireList->setOwner($data->getOwner());
        $desireList->setAccessRoles($data->getAccessRoles());
        $desireList->setName($data->getName());
        $desireList->setDescription($data->getDescription());
        foreach ($data->getDesires() as $desire){
            $desireList->addDesire($desire);
        }

        foreach ($data->getEvents() as $event){
            $desireList->addEvent($event);
        }

        return $desireList;
    }

    private function createNewInstance(): DesireList
    {
        return new DesireList();
    }
}
