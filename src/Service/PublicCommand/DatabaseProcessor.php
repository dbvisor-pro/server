<?php

declare(strict_types=1);

namespace App\Service\PublicCommand;

use App\Enum\LogStatusEnum;
use App\Exception\NoSuchMethodException;
use App\Service\AppLogger;
use App\Service\DumpManagement;
use App\ServiceApi\Actions\GetScheduledUID;
use App\ServiceApi\Actions\FinishDump;
use App\ServiceApi\Actions\GetDatabaseRules;
use DbManager\CoreBundle\DbProcessorFactory;
use DbManager\CoreBundle\Service\DbDataManager;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DbManager\CoreBundle\DBManagement\DBManagementFactory;

class DatabaseProcessor extends AbstractCommand
{
    /**
     * @param AppLogger $appLogger
     * @param GetScheduledUID $getScheduledUID
     * @param FinishDump $finishDump
     * @param DumpManagement $dumpManagement
     * @param DBManagementFactory $dbManagementFactory
     * @param DbProcessorFactory $processorFactory
     * @param GetDatabaseRules $getDatabaseRules
     */
    public function __construct(
        private readonly AppLogger       $appLogger,
        private readonly GetScheduledUID $getScheduledUID,
        private readonly FinishDump      $finishDump,
        private readonly DumpManagement   $dumpManagement,
        private readonly DBManagementFactory $dbManagementFactory,
        private readonly DbProcessorFactory $processorFactory,
        private readonly GetDatabaseRules $getDatabaseRules
    ) {
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws NoSuchMethodException
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->appLogger->initAppLogger($output);
        $scheduledData = $this->getScheduledUID->execute();
        if (!empty($scheduledData)) {
            if (empty($scheduledData['uuid']) || empty($scheduledData['db']['uid'])) {
                throw new \Exception("Something went wrong. Scheduled uuid and database uuid is required");
            }

            $this->appLogger->logToService(
                $scheduledData['uuid'],
                LogStatusEnum::PROCESSING->value,
                "Preparing backup"
            );
            $file = $this->dumpManagement->createDump($scheduledData['db']['uid']);

            $tempDatabase = 'temp_' . time();

            $database = new DbDataManager(
                array_merge(
                    $this->getDatabaseRules->get($scheduledData['db']['uid']),
                    [
                        'name' => $tempDatabase,
                        'inputFile' => $file->getPathname()
                    ]
                )
            );
            $dbManagement = $this->dbManagementFactory->create();
            $dbManagement->create($database);
            $dbManagement->import($database);

            $this->processorFactory->create($database->getEngine())->process($database);

            $dbManagement->dump($database);
            $dbManagement->drop($database);

            $this->finishDump->execute($scheduledData['uuid'], 'ready', $file->getFilename());
        }
    }
}
