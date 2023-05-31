<?php

declare(strict_types=1);

namespace DbManager\CoreBundle\DataProcessor;

use Illuminate\Database\Connection;

class DataProcessorFactory implements DataProcessorFactoryInterface
{
    public function create(string $tableName, Connection $connection): TableService
    {
        return new TableService($tableName, $connection);
    }
}