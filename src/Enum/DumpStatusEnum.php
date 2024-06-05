<?php

declare(strict_types=1);

namespace App\Enum;

enum DumpStatusEnum: string
{
    case SCHEDULED = 'scheduled';
    case PROCESSING = 'processing';
    case ERROR = 'error';
    case READY = 'ready';
    case READY_WITH_ERROR = 'ready_with_error';
}
