<?php

declare(strict_types=1);

namespace App\Service\PublicCommand;

use App\Enum\ServerStatusEnum;
use App\Service\AppConfig;
use App\Service\InputOutput;
use App\Service\ShellProcess;
use App\ServiceApi\Actions\GetUserByEmail;
use App\ServiceApi\Entity\Server;
use Exception;
use Psr\Cache\InvalidArgumentException;
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
     * @param AppConfig $appConfig
     * @param Server $serverApi
     * @param AddDatabase $addDatabase
     * @param ShellProcess $shellProcess
     * @param GetUserByEmail $getUserByEmail
     * @param HttpClientInterface $httpClient
     */
    public function __construct(
        private readonly AppConfig $appConfig,
        private readonly Server $serverApi,
        private readonly AddDatabase $addDatabase,
        private readonly ShellProcess $shellProcess,
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
        $this->initInputOutput($input, $output);

        $this->validateRequiredLibs();
        if ($this->addServerInfo()) {
            $this->addDatabase->execute($input, $output);
        }
    }

    protected function validateRequiredLibs()
    {
        $inputOutput = $this->getInputOutput();

        $inputOutput->info('Validating required software...');
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function addServerInfo(): bool
    {
        $inputOutput = $this->getInputOutput();

        try {
            $userEmail  = $inputOutput->ask("Enter your Email");
            $password   = $inputOutput->askHidden("Enter your Password");
            $user       = $this->getUserByEmail->setCredentials($userEmail, $password)->execute($userEmail);

            $action = $inputOutput->choice(
                "Would you like to do: add - new server or update - existed server?",
                [
                    'add',
                    'update'
                ]
            );

            $server = match ($action) {
                'add'    => $this->createServer($inputOutput, $user, $userEmail, $password),
                'update' => $this->updateServer($inputOutput, $user, $userEmail, $password)
            };

            $this->updateEnvFile($server['uuid'], $server['secret_key']);

            return true;
        } catch (
            Exception
            | ClientExceptionInterface
            | DecodingExceptionInterface
            | ServerExceptionInterface
            | TransportExceptionInterface
            | InvalidArgumentException
            | RedirectionExceptionInterface $e
        ) {
            // TODO: Need to add clear error;
            $inputOutput->error("During updating server an error happened: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Create new server
     *
     * @param InputOutput $inputOutput
     * @param array $user
     * @param string $userEmail
     * @param string $password
     *
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws InvalidArgumentException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function createServer(InputOutput $inputOutput, array $user, string $userEmail, string $password): array
    {
        $serverName = $inputOutput->ask("Enter server name");

        $server = $this->serverApi->setCredentials($userEmail, $password)->create(
            [
                'name'        => $serverName,
                'status'      => ServerStatusEnum::ENABLED->value,
                'ipAddress'   => $this->getIpAddress(),
                'workspaceId' => $this->getWorkspaceId($user, $inputOutput)
            ]
        );

        $inputOutput->success("Server successfully added");
        return $server;
    }

    /**
     * Update / activate existed server
     *
     * @param InputOutput $inputOutput
     * @param array $user
     * @param string $userEmail
     * @param string $password
     *
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws InvalidArgumentException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    private function updateServer(InputOutput $inputOutput, array $user, string $userEmail, string $password): array
    {
        $uuid   = $inputOutput->ask("Enter server UUID");
        $server = $this->serverApi->setCredentials($userEmail, $password)->get(htmlspecialchars($uuid));

        $serverWorkspace = (int)str_replace('/api/workspaces/', '', $server['workspaceId']);
        if (!in_array($serverWorkspace, array_column($user['workspaces'], 'id'))) {
            throw new Exception('You do not have access to this server!!!');
        }

        $server = $this->serverApi->setCredentials($userEmail, $password)->update(
            $uuid,
            [
                'status'      => ServerStatusEnum::ENABLED->value,
                'ipAddress'   => $this->getIpAddress()
            ]
        );

        $inputOutput->success("Server successfully updated");
        return $server;
    }

    /**
     * Get workspace ID
     *
     * @param array $user
     * @param InputOutput $inputOutput
     *
     * @return string
     */
    private function getWorkspaceId(array $user, InputOutput $inputOutput): string
    {
        if (count($user['workspaces']) == 1) {
            return "/api/workspaces/" . $user['workspaces'][0]['id'];
        }

        $workspaceCode = $inputOutput->choice(
            "Select Workspace",
            array_column($user['workspaces'], 'code')
        );

        $key = array_search($workspaceCode, array_column($user['workspaces'], 'code'));

        return "/api/workspaces/" . $user['workspaces'][$key]['id'];
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

    /**
     * Update ENV file
     *
     * @param string $newUuid
     * @param string $newSKey
     *
     * @return void
     * @throws Exception
     */
    private function updateEnvFile(string $newUuid, string $newSKey): void
    {
        $envFile = $this->appConfig->getProjectDir() . '/.env';

        $uuid = $this->appConfig->getServerUuid();
        $command = 'sed -e "s/^APP_SERVER_UUID=' . $uuid . '/APP_SERVER_UUID=' . $newUuid . '/g"';
        $this->shellProcess->run($command . ' ' . $envFile . ' > ' . $envFile . '.tmp');
        $this->shellProcess->run(' cat ' . $envFile . '.tmp > ' . $envFile);
        $this->shellProcess->run(' rm ' . $envFile . '.tmp');

        $secretKey = $this->appConfig->getServerSecretKey();
        $command = 'sed -e "s/^APP_SERVER_SECRET_KEY=' . $secretKey . '/APP_SERVER_SECRET_KEY=' . $newSKey . '/g"';
        $this->shellProcess->run($command . ' ' . $envFile . ' > ' . $envFile . '.tmp');
        $this->shellProcess->run(' cat ' . $envFile . '.tmp > ' . $envFile);
        $this->shellProcess->run(' rm ' . $envFile . '.tmp');

        $this->appConfig->updateEnvConfigs();
    }
}
