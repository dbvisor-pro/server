<?php

declare(strict_types=1);

namespace App\Service\Database;

use DbManager\CoreBundle\DbProcessorFactory;
use DbManager\CoreBundle\Exception\EngineNotSupportedException;
use DbManager\CoreBundle\Exception\NoSuchEngineException;
use DbManager\CoreBundle\Service\DbDataManager;
use App\ServiceApi\Actions\GetDatabaseRules;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class Processor
{
    /**
     * @param GetDatabaseRules $getDatabaseRules
     * @param DbProcessorFactory $processorFactory
     */
    public function __construct(
        private readonly GetDatabaseRules $getDatabaseRules,
        private readonly DbProcessorFactory $processorFactory
    ) {
    }

    /**
     * @param string $databaseUid
     * @param string $tempDatabase
     *
     * @return void
     * @throws NoSuchEngineException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws EngineNotSupportedException
     */
    public function process(string $databaseUid, string $tempDatabase): void
    {
        $dbManager =  new DbDataManager(
            array_merge(
                [
                    'name' => $tempDatabase
                ],
                $this->getDatabaseRules->get($databaseUid)
            )
        );

        $this->processorFactory->create($dbManager->getEngine())->process($dbManager);
    }
}
