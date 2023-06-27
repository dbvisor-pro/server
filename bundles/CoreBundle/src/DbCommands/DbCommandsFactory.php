<?php

declare(strict_types=1);

namespace DbManager\CoreBundle\DbCommands;

use DbManager\CoreBundle\Exception\NoSuchEngineException;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Dump Processor Factory
 */
final class DbCommandsFactory
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    /**
     * @return DbCommandsInterface
     * @throws NoSuchEngineException
     */
    public function create(): DbCommandsInterface
    {
        $engine = env('DATABASE_ENGINE');

        return $this->getEngine($engine);
    }

    /**
     * Retrieve engine service according to provided engine name
     *
     * @param string $engine
     *
     * @return DbCommandsInterface
     *
     * @throws NoSuchEngineException
     */
    private function getEngine(string $engine): object
    {
        $serviceName = $this->getServiceName($engine);

        if (!$this->container->has($serviceName)) {
            throw new NoSuchEngineException(sprintf("No such engine %s", $engine));
        }

        $engine = $this->container->get($serviceName);
        if (!($engine instanceof DbCommandsInterface)) {
            throw new InvalidArgumentException('The engine must be instance of EngineInterface');
        }

        return $engine;
    }

    /**
     * Due to service name agreement return service name
     *
     * @param string $engine
     *
     * @return string
     */
    private function getServiceName(string $engine): string
    {
        return sprintf("db_manager_core.dump.engines.%s", $engine);
    }
}
