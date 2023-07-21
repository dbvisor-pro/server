<?php

declare(strict_types=1);

namespace App\Service\Engines;

use App\Enum\LogStatusEnum;
use DbManager\CoreBundle\Exception\NoSuchEngineException;
use Exception;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class Mysql extends AbstractEngine implements EngineInterface
{
    /**
     * @param string $dumpuuid
     * @param string $dbuuid
     * @param string $filename
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws NoSuchEngineException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function execute(string $dumpuuid, string $dbuuid, string $filename): void
    {
        $tempDbName = 'temp_' . time();
//        $workDbPassword = $this->getWorkDbPassowrd();

        $dbCredentials = $this->getDatabaseCredentials($tempDbName);


        $originFile = $this->appConfig->getDumpUntouchedDirectory() . '/' . $dbuuid . '/' . $filename;
        $destinationFile = $this->appConfig->getDumpProcessedDirectory() . '/' . $dbuuid . '/' . $filename;
        /*$workDbPassword = $this->appConfig->getConfig('work_db_password')
            ? sprintf("-p%s", $this->appConfig->getConfig('work_db_password'))
            : '';*/

        $this->appLogger->logToService(
            $dumpuuid,
            LogStatusEnum::PROCESSING->value,
            "Creating temporary database"
        );

        $this->setupTemporaryDatabase($tempDbName, $originFile);

/*        $this->shellProcess->run(sprintf(
            "mysql -u%s %s -h%s -P%s -e 'CREATE DATABASE %s'",
            $this->appConfig->getConfig('work_db_user'),
            $workDbPassword,
            $this->appConfig->getConfig('work_db_host'),
            $this->appConfig->getConfig('work_db_port'),
            $tempDbName
        ));

        $this->appLogger->logToService(
            $dumpuuid,
            LogStatusEnum::PROCESSING->value,
            "Import backup to temp database"
        );
        $this->shellProcess->run(sprintf(
            "mysql -u%s %s -h%s -P%s %s < %s",
            $this->appConfig->getConfig('work_db_user'),
            $workDbPassword,
            $this->appConfig->getConfig('work_db_host'),
            $this->appConfig->getConfig('work_db_port'),
            $tempDbName,
            $originFile
        ));*/

        $this->appLogger->logToService(
            $dumpuuid,
            LogStatusEnum::PROCESSING->value,
            "Process temp database"
        );

        $this->appLogger->logToService(
            $dumpuuid,
            LogStatusEnum::PROCESSING->value,
            "Import backup to temp database"
        );

        $this->runProcessor($dbuuid, $tempDbName);

        $this->appLogger->logToService(
            $dumpuuid,
            LogStatusEnum::PROCESSING->value,
            "Creating dump"
        );

        $this->shellProcess->run(sprintf(
            "mysqldump -u%s %s -h%s -P%s %s > %s",
            ...[...$dbCredentials, $destinationFile]
        ));

        $this->appLogger->logToService(
            $dumpuuid,
            LogStatusEnum::PROCESSING->value,
            "Dropping temporary database"
        );

        $this->dropTemporaryDatabase($tempDbName);
    }

    public function setupTemporaryDatabase(string $tempDbName, string $originFile): void
    {
//        $workDbPassword = $this->getWorkDbPassowrd();
        $dbCredentials = $this->getDatabaseCredentials($tempDbName);

        $this->shellProcess->run(sprintf(
            "mysql -u%s %s -h%s -P%s -e 'CREATE DATABASE %s'",
            ...[...$dbCredentials, $tempDbName]
        ));

        $this->shellProcess->run(sprintf(
            "mysql -u%s %s -h%s -P%s %s < %s",
            ...[...$dbCredentials, $originFile]
        ));
    }

    public function dropTemporaryDatabase(string $tempDbName): void
    {
        $dbCredentials = $this->getDatabaseCredentials($tempDbName);
        $this->shellProcess->run(sprintf(
            "mysql -u%s %s -h%s -P%s -e 'DROP DATABASE %s'",
            ...$dbCredentials
        ));
    }

    private function getDatabaseCredentials(string $dbName): array
    {
        $workDbPassword = $this->getWorkDbPassword();
        return [
            $this->appConfig->getConfig('work_db_user'),
            $workDbPassword,
            $this->appConfig->getConfig('work_db_host'),
            $this->appConfig->getConfig('work_db_port'),
            $dbName,
        ];
    }

    private function getWorkDbPassword(): string
    {
        return $this->appConfig->getConfig('work_db_password')
            ? sprintf("-p%s", $this->appConfig->getConfig('work_db_password'))
            : '';
    }
}
