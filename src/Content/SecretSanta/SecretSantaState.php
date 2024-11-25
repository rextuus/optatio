<?php
declare(strict_types=1);

namespace App\Content\SecretSanta;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
enum SecretSantaState: string
{
    case OPEN = 'open';
    case PHASE_1 = 'phase_1';
    case PHASE_2 = 'phase_2';
    case RUNNING = 'running';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::OPEN => 'Anmeldephase',
            self::PHASE_1 => 'Wichtelziehung 1',
            self::PHASE_2 => 'Wichtelziehung 2',
            self::RUNNING => 'Wichtelei in vollem Gange',
        };
    }
}
