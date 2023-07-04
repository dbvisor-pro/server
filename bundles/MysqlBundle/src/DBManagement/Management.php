<?php

declare(strict_types=1);

namespace DbManager\MysqlBundle\DBManagement;

use DbManager\CoreBundle\DBManagement\AbstractDBManagement;
use DbManager\CoreBundle\DBManagement\DBManagementInterface;

/**
 * Mysql Dump Processor instance
 */
final class Management extends AbstractDBManagement implements DBManagementInterface
{
    protected function getDropLine(string $dbName): string
    {
        return sprintf(
            "mysql -u%s %s -h%s -P%s -e 'DROP DATABASE %s'",
            $this->appConfig->getConfig('work_db_user'),
            $this->getPassword(),
            $this->appConfig->getConfig('work_db_host'),
            $this->appConfig->getConfig('work_db_port'),
            escapeshellarg($dbName)
        );
    }

    protected function getCreateLine(string $dbName): string
    {
        return sprintf(
            "mysql -u%s %s -h%s -P%s -e 'CREATE DATABASE %s'",
            $this->appConfig->getConfig('work_db_user'),
            $this->getPassword(),
            $this->appConfig->getConfig('work_db_host'),
            $this->appConfig->getConfig('work_db_port'),
            escapeshellarg($dbName)
        );
    }

    protected function getImportLine(string $dbName, string $inputPath): string
    {
        return sprintf(
            "mysql -u%s %s -h%s -P%s %s < %s",
            $this->appConfig->getConfig('work_db_user'),
            $this->getPassword(),
            $this->appConfig->getConfig('work_db_host'),
            $this->appConfig->getConfig('work_db_port'),
            escapeshellarg($dbName),
            escapeshellarg($inputPath)
        );
    }

    protected function getDumpLine(string $dbName, string $outputPath): string
    {
        return sprintf(
            'mysqldump -h%s -p%s -u%s -P%s %s > %s',
            escapeshellarg(env('DATABASE_HOST')),
            escapeshellarg(env('DATABASE_PASSWD')),
            escapeshellarg(env('DATABASE_USER')),
            escapeshellarg(env('DATABASE_PORT')),
            escapeshellarg($dbName),
            escapeshellarg($outputPath),
        );
    }
}
