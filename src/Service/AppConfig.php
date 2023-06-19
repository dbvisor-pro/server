<?php

declare(strict_types=1);

namespace App\Service;

use Dotenv\Dotenv;
use Symfony\Component\HttpKernel\KernelInterface;
use \Exception;

class AppConfig
{
    private array $databaseConfig = [];

    private ?array $defaultConfig = null;

    public function __construct(
        private readonly  KernelInterface $kernel,
        array $config
    ) {
        $this->defaultConfig = $config;
    }

    /**
     * @param string $dbUuid
     * @return array
     * @throws \Exception
     */
    public function getDatabaseConfig(string $dbUuid): array
    {
        if (empty($this->databaseConfig[$dbUuid])) {
            $this->databaseConfig[$dbUuid] = array_change_key_case(
                $this->getConfigFile($this->getConfigDirectory() . '/' . $dbUuid, 'config')
            );
        }

        return $this->databaseConfig[$dbUuid] ?? [];
    }

    /**
     * @param string $config
     * @return string|null
     */
    public function getConfig(string $config): null | string
    {
//        if ($this->defaultConfig === null) {
////            $this->defaultConfig = array_change_key_case($this->getConfigFile($this->getConfigDirectory(), 'default'));
//        }

        return $this->defaultConfig[strtolower($config)] ?? null;
    }

    /**
     * @return string
     */
    public function getDumpUntouchedDirectory(): string
    {
        return $this->getAppRootDir() . '/dumps/untouched';
    }

    /**
     * @return string
     */
    public function getDumpProcessedDirectory(): string
    {
        return $this->getAppRootDir() . '/dumps/processed';
    }

    /**
     * @return string
     */
    public function getKeyFilePath(): string
    {
        return $this->getConfigDirectory() . '/keys';
    }

    public function getConfigDirectory(): string
    {
        return $this->getAppRootDir() . '/config';
    }

    /**
     * Retrieve whole application root directory (not symfony)
     *
     * @return string
     */
    public function getAppRootDir(): string
    {
        return $this->kernel->getProjectDir() . '/../..';
    }

    /**
     * @param string $directory
     * @param string $file
     * @return array
     */
    private function getConfigFile(string $directory, string $file): array
    {
        $dotenv = Dotenv::createImmutable($directory, $file);
        return $dotenv->load();
    }
}
