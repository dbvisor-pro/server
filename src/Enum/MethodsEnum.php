<?php

declare(strict_types=1);

namespace App\Enum;

enum MethodsEnum: string
{

    case MANUAL = 'manual';
    case DUMP = 'dump';
    case SSH_DUMP = 'ssh-dump';

    public function description(string $engine): string
    {
        return match($this) {
            MethodsEnum::DUMP => sprintf("%s server located at current server. Use regular mysqldump command", $engine),
            MethodsEnum::SSH_DUMP => sprintf("%s server located at remote server. Dump over SSH", $engine),
            MethodsEnum::MANUAL => "Configure manual dump deployment"
        };
    }

}
