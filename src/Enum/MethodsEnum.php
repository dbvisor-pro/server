<?php

declare(strict_types=1);

namespace App\Enum;

enum MethodsEnum: string
{

    case MANUAL = 'manual';
    case DUMP = 'dump';
    case DUMP_OVER_SSH = 'ssh-dump';

}
