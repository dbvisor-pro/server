<?php

declare(strict_types=1);

namespace App\Service\Engines;

use App\Service\AppLogger;
use App\Service\ShellProcess;
use App\Service\AppConfig;
use DbManager\CoreBundle\Exception\NoSuchEngineException;
use DbManager\CoreBundle\Processor;
use DbManager\CoreBundle\Service\DbDataManager;
use App\ServiceApi\Actions\GetDatabaseRules;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

abstract class AbstractEngine
{

    /**
     * @param ShellProcess $shellProcess
     * @param AppConfig $appConfig
     * @param Processor $processor
     * @param GetDatabaseRules $getDatabaseRules
     */
    public function __construct(
        protected readonly ShellProcess $shellProcess,
        protected readonly AppConfig $appConfig,
        protected readonly AppLogger $appLogger,
        private readonly Processor $processor,
        private readonly GetDatabaseRules $getDatabaseRules,
    ) {
    }

    /**
     * @param string $databaseUid
     * @param string $tempDatabase
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws NoSuchEngineException
     */
    public function runProcessor(string $databaseUid, string $tempDatabase): void
    {
        $this->processor->execute(
            new DbDataManager(
                array_merge(
                    [
                        'name' => $tempDatabase
                    ],
                    $this->getDatabaseRules->get($databaseUid)
                )
            )
        );
    }
}
