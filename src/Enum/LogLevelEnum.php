<?php

declare(strict_types=1);

namespace App\Enum;

enum LogLevelEnum: string
{
    case INFO = 'info';
    case ERROR = 'error';
    case DEBUG = 'debug';
}
