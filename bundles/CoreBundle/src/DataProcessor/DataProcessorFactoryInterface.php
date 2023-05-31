<?php

declare(strict_types=1);

namespace DbManager\CoreBundle\DataProcessor;

use Illuminate\Database\Connection;

interface DataProcessorFactoryInterface
{
    public function create(string $tableName, Connection $connection): TableService;
}