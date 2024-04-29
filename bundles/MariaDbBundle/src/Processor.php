<?php

declare(strict_types=1);

namespace DbManager\MariaDbBundle;

use DbManager\CoreBundle\Interfaces\EngineInterface;
use DbManager\MysqlBundle\Processor as MySQLProcessor;

/**
 * MariaDB Processor instance
 */
class Processor extends MySQLProcessor implements EngineInterface
{
    /**
     * Engine const
     * There must be MySQL as it is used in core PDO module for connection to DB
     */
    public const DRIVER_ENGINE = 'mysql';
}
