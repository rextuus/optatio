<?php

declare(strict_types=1);

namespace App\Content\DesireList\Data;

use App\Content\Desire\ActionType;
use App\Entity\Desire;
use App\Entity\DesireList;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2024 DocCheck Community GmbH
 */
class DesireCopyData
{
    #[Assert\NotBlank()]
    private DesireList $from;
    #[Assert\NotBlank()]
    private DesireList $to;

    /**
     * @var array<Desire>
     */
    #[Assert\Count(
        min: 1,
        minMessage: 'Du musst mindestens einen Wunsch auswählen!'
    )]
    private array $desires = [];

    private ActionType $action;

    public function getFrom(): DesireList
    {
        return $this->from;
    }

    public function setFrom(DesireList $from): DesireCopyData
    {
        $this->from = $from;
        return $this;
    }

    public function getTo(): DesireList
    {
        return $this->to;
    }

    public function setTo(DesireList $to): DesireCopyData
    {
        $this->to = $to;
        return $this;
    }

    public function getDesires(): array
    {
        return $this->desires;
    }

    public function setDesires(array $desires): DesireCopyData
    {
        $this->desires = $desires;
        return $this;
    }

    public function getAction(): ActionType
    {
        return $this->action;
    }

    public function setAction(ActionType $action): DesireCopyData
    {
        $this->action = $action;
        return $this;
    }
}
