<?php

declare(strict_types=1);

namespace App\Service\Engine;

abstract class AbstractEngine implements EngineInterface
{
     public abstract function getCode(): string;
}
