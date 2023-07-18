<?php

declare(strict_types=1);

namespace App\Service\PublicCommand;

use App\Service\AppConfig;
use App\Service\AppLogger;
use App\ServiceApi\Actions\AddServer;
use App\ServiceApi\Actions\GetUserByEmail;
use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;
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
     * Get IP
     */
    public const GET_IP_URL = 'https://ipecho.net/plain';

    /**
     * @var array
     */
    private array $config = [];

    /**
     * @param AppLogger $appLogger
     * @param AppConfig $appConfig
     * @param AddServer $addServer
     * @param GetUserByEmail $getUserByEmail
     * @param HttpClientInterface $httpClient
     */
    public function __construct(
        private readonly AppLogger $appLogger,
        private readonly AppConfig $appConfig,
        private readonly AddServer $addServer,
        private readonly GetUserByEmail $getUserByEmail,
        private readonly HttpClientInterface $httpClient
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

        $userEmail  = $inputOutput->ask("Enter your Email");
        $password   = $inputOutput->askHidden("Enter your Password");
        $serverName = $inputOutput->ask("Enter server name");

        $user   = $this->getUserByEmail->setCredentials($userEmail, $password)->execute($userEmail);
        $server = $this->addServer->execute(
            [
                'name' => $serverName,
                'status' => 'pending',
                'ip_address' => $this->getIpAddress(),
                'workspace_id_id' => $user['workspace']['id'] // What if customer has a few Workspaces ???
            ]
        );

        $inputOutput->success("Server successfully added, please set next parameters in .env file:");
        $message = sprintf(
            "please set next parameters in .env file: APP_SERVER_UUID: %s and APP_SERVER_SECRET_KEY: %s",
            $server['uuid'],
            $server['secret_key']
        );
        $inputOutput->note($message);
        // Generate server info
        // Send data about server to service
    }

    /**
     * Get IP address
     *
     * @return string
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getIpAddress(): string
    {
        try {
            return $this->httpClient->request('GET', self::GET_IP_URL)->getContent();
        } catch (\Exception $e) {
            return '127.0.0.1';
        }
    }
}
