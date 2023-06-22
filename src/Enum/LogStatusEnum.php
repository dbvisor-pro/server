<?php

declare(strict_types=1);

namespace App\Enum;

enum LogStatusEnum: string
{

    case READY = 'ready';
    case ERROR = 'error';
    case PROCESSING = 'processing';

}
