<?php

declare(strict_types=1);

namespace App\Content\Desire;

enum ActionType: string
{
    case VERSCHIEBEN = 'verschieben';
    case KOPIEREN = 'kopieren';
    case TEILEN = 'teilen';

    public function getLabel(): string
    {
        return match ($this) {
            self::TEILEN => 'Teilen',
            self::KOPIEREN => 'Kopieren',
            self::VERSCHIEBEN => 'Verschieben',
        };
    }
}