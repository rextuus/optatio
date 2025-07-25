<?php

declare(strict_types=1);

namespace App\Content\DesireList\Relation;

/**
 * Enum representing the type of relation between desire lists.
 */
enum DesireListRelationType: string
{
    /**
     * Represents a shared desire between lists.
     */
    case SHARED = 'shared';
    
    /**
     * Represents a copied desire between lists.
     */
    case COPIED = 'copied';
    
    /**
     * Represents a moved desire between lists.
     */
    case MOVED = 'moved';
}