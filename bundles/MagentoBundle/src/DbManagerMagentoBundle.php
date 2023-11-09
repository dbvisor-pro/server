<?php

declare(strict_types=1);

namespace DbManager\MagentoBundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use DbManager\MagentoBundle\DependencyInjection\DbManagerMagentoExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class DbManagerMagentoBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new DbManagerMagentoExtension($this);
    }
}
