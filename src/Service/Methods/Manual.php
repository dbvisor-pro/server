<?php

declare(strict_types=1);

namespace App\Service\Methods;

use App\Exception\DumpNotFoundException;
use App\Service\InputOutput;

class Manual extends AbstractMethod
{
    /**
     * @param array $dbConfig
     * @param string $dbUuid
     * @param string|null $filename
     * @return string
     * @throws DumpNotFoundException
     */
    public function execute(array $dbConfig, string $dbUuid, ?string $filename = null): string
    {
        $originFile = $dbConfig['dump_name'];
        if (!is_file($originFile)) {
            $originFile = $this->getOriginFile($dbUuid, $dbConfig['dump_name']);
        }

        if (!$filename) {
            $filename = time() . '.sql';
        }
        $destFile = $this->getOriginFile($dbUuid, $filename);

        if (!is_file($originFile)) {
            throw new DumpNotFoundException("Dump file not found");
        }

        copy($originFile, $destFile);
//        rename($originFile, $destFile);

        return $destFile;
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
        $config = [];
        $config['dump_name'] = $inputOutput->ask('Enter full path to DB dump file?');
        return $config;
    }

    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return 'manual';
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Configure manual dump deployment';
    }
}
