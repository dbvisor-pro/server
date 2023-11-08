<?php

declare(strict_types=1);

namespace DbManager\TestBundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use DbManager\TestBundle\DependencyInjection\DbManagerTestExtension;
class DbManagerTestBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new DbManagerTestExtension($this);
    }
}
