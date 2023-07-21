<?php

declare(strict_types=1);

namespace App\Service\PublicCommand\Database;

use App\Service\AppConfig;
use App\Service\Engines\Engine;
use App\Service\Methods\Method;
use App\Service\PublicCommand\AbstractCommand;
use App\ServiceApi\Actions\GetDatabaseRules;
use App\ServiceApi\Actions\SendDbStructure;
use DbManager\CoreBundle\DbProcessorFactory;
use DbManager\CoreBundle\Exception\EngineNotSupportedException;
use DbManager\CoreBundle\Exception\NoSuchEngineException;
use DbManager\CoreBundle\Service\DbDataManager;
use Doctrine\DBAL\Exception;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Analyzer extends AbstractCommand
{
    /**
     * @param AppConfig $appConfig
     * @param SendDbStructure $sendDbStructure
     * @param GetDatabaseRules $getDatabaseRules
     * @param DbProcessorFactory $processorFactory
     * @param Engine $engine
     * @param Method $method
     */
    public function __construct(
        private readonly AppConfig $appConfig,
        private readonly SendDbStructure $sendDbStructure,
        private readonly GetDatabaseRules $getDatabaseRules,
        private readonly DbProcessorFactory $processorFactory,
        private readonly Engine $engine,
        private readonly Method $method
    ) {
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws EngineNotSupportedException
     * @throws Exception
     * @throws NoSuchEngineException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->initInputOutput($input, $output);

        $tempDatabase = $input->getOption('db');
        $databaseUid = $input->getOption('uid');

        if (!$tempDatabase) {
            $this->createTempDbAndProcess($databaseUid);
        } else {
            $this->process($databaseUid, $tempDatabase);
        }

    }

    /**
     * Process without database
     *
     * @param string $databaseUid
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function createTempDbAndProcess(string $databaseUid): void
    {
        try {
            $dbInfo = $this->getDbInfo($databaseUid);
            $io = $this->getInputOutput();

            $tempDatabase = 'temp_' . time();
            $filename = time() . '.sql';

            $io->info("Dump database...");
            $method = $this->method->getMethodClass($dbInfo['method']);
            $method->execute($dbInfo, $databaseUid, $filename);

            /** @var \App\Service\Engines\Mysql $engine */
            $io->info("Import temporary database...");
            $engine = $this->engine->getEngineClass($dbInfo['engine']);
            $originFile = $this->appConfig->getDumpUntouchedDirectory() . '/' . $databaseUid . '/' . $filename;

            $engine->setupTemporaryDatabase($tempDatabase, $originFile);

            $io->info("Analyzing...");
            $this->process($databaseUid, $tempDatabase);

            $io->info("Drop temporary database...");
            $engine->dropTemporaryDatabase($tempDatabase);
        } catch (\Exception $exception) {
            $this->getInputOutput()->error($exception->getMessage());
        }
    }

    /**
     * @param string $databaseUid
     * @param string $tempDatabase
     *
     * @return void
     * @throws ClientExceptionInterface
     * @throws EngineNotSupportedException
     * @throws NoSuchEngineException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function process(string $databaseUid, ?string $tempDatabase = null): void
    {
        $dbInfo = $this->getDbInfo($databaseUid);

        $dbManager = new DbDataManager(
            array_merge(
                $dbInfo,
                [
                    'name' => $tempDatabase,
                ]
            )
        );

        $structure = $this->processorFactory->create($dbManager->getEngine())->getDbStructure($dbManager);
        $this->sendDbStructure->execute($databaseUid, $structure);
    }

    /**
     * Get DB info
     *
     * @param string $databaseUid
     *
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    private function getDbInfo(string $databaseUid): array
    {
        try {
            $dbData = $this->appConfig->getDatabaseConfig($databaseUid);
            if (!$dbData['platform']) {
                $dbData['platform'] = 'custom';
            }

            return $dbData;
        } catch (\Exception $e) {
            return $this->getDatabaseRules->get($databaseUid);
        }
    }
}
