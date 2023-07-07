<?php

declare(strict_types=1);

namespace App\Service\PublicCommand;

use App\Enum\MethodsEnum;
use App\Service\AppConfig;
use App\Service\AppLogger;
use App\Service\Database\Analyzer;
use App\Service\InputOutput;
use App\ServiceApi\Actions\AddDatabase as ServiceApiAddDatabase;
use App\ServiceApi\Actions\GetAccessToken;
use DbManager\CoreBundle\DBManagement\DBManagementFactory;
use DbManager\CoreBundle\Exception\EngineNotSupportedException;
use DbManager\CoreBundle\Exception\NoSuchEngineException;
use DbManager\CoreBundle\Exception\ShellProcessorException;
use DbManager\CoreBundle\Service\DbDataManager;
use Exception;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallApp extends AbstractCommand
{
    /**
     * @var array
     */
    private array $config = [];

    /**
     * @param AppLogger $appLogger
     * @param GetAccessToken $accessToken
     */
    public function __construct(
        private readonly AppLogger $appLogger,
        private readonly GetAccessToken $getAccessToken
    ) {
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->appLogger->initAppLogger($output);
        $this->initInputOutput($input, $output);

        $this->validateRequiredLibs();
        $this->addServerInfo();
    }

    protected function validateRequiredLibs()
    {
        $inputOutput = $this->getInputOutput();

        $inputOutput->info('Validating required software...');
    }

    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    protected function addServerInfo(): void
    {
        $inputOutput = $this->getInputOutput();

        $userName = $inputOutput->ask("Enter your Username");
        $password = $inputOutput->askHidden("Enter your Password");

        $token = $this->getAccessToken->execute($userName, $password);

        // Generate server info
        // Send data about server to service
    }
}
