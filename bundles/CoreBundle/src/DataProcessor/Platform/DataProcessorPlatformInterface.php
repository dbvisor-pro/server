<?php

declare(strict_types=1);

namespace DbManager\CoreBundle\DataProcessor\Platform;

interface DataProcessorPlatformInterface
{
    /**
     * @return string
     */
    public function getKey(): string;
}
