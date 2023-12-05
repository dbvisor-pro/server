<?php

declare(strict_types=1);

namespace DbManager\CoreBundle\Enums;

enum ErrorSeverityEnum: int
{
    case ERROR = 0;
    case WARNING = 1;
    case NOTICE = 2;

    public function title(): string
    {
        return match ($this) {
            self::ERROR => "Error",
            self::WARNING => "Warning",
            self::NOTICE => "Notice"
        };
    }
}
