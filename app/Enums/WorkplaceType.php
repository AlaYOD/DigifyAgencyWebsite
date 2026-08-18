<?php

namespace App\Enums;

enum WorkplaceType: string
{
    case ON_SITE = 'on_site';
    case HYBRID = 'hybrid';
    case REMOTE = 'remote';
}
