<?php

namespace App\Enums;

enum JobStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case PAUSED = 'paused';
    case CLOSED = 'closed';
    case ARCHIVED = 'archived';
}
