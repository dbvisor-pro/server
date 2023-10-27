<?php

declare(strict_types=1);

namespace App\Service\Methods;

use App\Service\InputOutput;
use \Exception;

class DumpOverSsh extends AbstractMethod
{
    const AUTH_TYPE_KEY = 'key';
    const AUTH_TYPE_PASS = 'password';

    /**
     * @param array $dbConfig
     * @param string $dbUuid
     * @param string|null $filename
     * @return string
     * @throws Exception
     */
    public function execute(array $dbConfig, string $dbUuid, ?string $filename = null): string
    {


        return '';
    }

    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return 'ssh-dump';
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Database located at remote server. Dump over SSH';
    }

    /**
     * @inheritDoc
     */
    public function validate(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function askConfig(InputOutput $inputOutput): array
    {
        return [
            ...$this->askDatabaseConfig($inputOutput),
            ...$this->askSSHConfig($inputOutput)
        ];
    }

    private function askSSHConfig(InputOutput $inputOutput): array
    {
        $validateRequired = function ($value) {
            if (empty($value)) {
                throw new \RuntimeException('Value is required.');
            }

            return $value;
        };
        $config = [];

        $config['ssh_host'] = $inputOutput->ask('SSH Host:', '', $validateRequired);
        $config['ssh_user'] = $inputOutput->ask('SSH User:', '', $validateRequired);
        $config['ssh_auth'] = $inputOutput->choice("Select authentication method:", [
            self::AUTH_TYPE_KEY => 'SSH Key',
            self::AUTH_TYPE_PASS => 'Password'
        ]);

        if ($config['ssh_auth'] === self::AUTH_TYPE_KEY) {
            $config['ssh_key_path'] = $inputOutput->ask('Key path:', '~/.ssh/id_rsa', $validateRequired);
        } elseif ($config['ssh_auth'] === self::AUTH_TYPE_PASS) {
            $config['ssh_password'] = $inputOutput->askHidden('SSH Password:', $validateRequired);
        } else {
            $inputOutput->error("Something went wrong. Method is not specified");
            exit;
        }

        $config['ssh_port'] = $inputOutput->ask('SSH Port:', '22', $validateRequired);

        return $config;
    }

    /**
     * @param InputOutput $inputOutput
     * @return array
     */
    private function askDatabaseConfig(InputOutput $inputOutput): array
    {
        $validateRequired = function ($value) {
            if (empty($value)) {
                throw new \RuntimeException('Value is required.');
            }

            return $value;
        };
        $config = [];

        $config['db_host'] = $inputOutput->ask('Database Host', 'localhost', $validateRequired);
        $config['db_user'] = $inputOutput->ask('Database User:', 'root', $validateRequired);
        $config['db_password'] = $inputOutput->askHidden('Password');
        $config['db_name'] = $inputOutput->ask('Database name:', null, $validateRequired);
        $config['db_port'] = $inputOutput->ask('Database Port: ', '3306', $validateRequired);

        return $config;
    }
}
