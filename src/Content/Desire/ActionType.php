<?php

declare(strict_types=1);

namespace App\Content\Desire;

enum ActionType: string
{
    case TEILEN = 'teilen';
    case KOPIEREN = 'kopieren';
    case VERSCHIEBEN = 'verschieben';

    public function getLabel(): string
    {
        return match ($this) {
            self::TEILEN => 'Teilen',
            self::KOPIEREN => 'Kopieren',
            self::VERSCHIEBEN => 'Verschieben',
        };
    }
}