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
}
