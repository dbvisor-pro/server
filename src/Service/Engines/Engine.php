<?php

declare(strict_types=1);

namespace App\Service\Engines;

use App\Exception\NoSuchMethodException;

class Engine
{
    /**
     * TODO: replace by service
     */
    public function __construct(
        private readonly Mysql $mysql,
    ) {
    }

    /**
     * TODO: implement return of services via container instead of construct
     *
     * @param string $engine
     * @return EngineInterface
     * @throws NoSuchMethodException
     */
    public function getEngineClass(string $engine): EngineInterface
    {
        return match ($engine) {
            'mysql' => $this->mysql,
            default => throw new NoSuchMethodException(sprintf("Engine %s is not exists", $engine)),
        };
    }
}
