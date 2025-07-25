<?php

namespace App\Content\Desire\ImageExtraction;

enum PicsExtractionState: string
{
    case PENDING = 'pending';
    case DONE = 'done';
}
