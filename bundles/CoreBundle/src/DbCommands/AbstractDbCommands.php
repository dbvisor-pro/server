<?php

declare(strict_types=1);

namespace DbManager\CoreBundle\DbCommands;

use App\Service\AppConfig;
use DbManager\CoreBundle\Exception\ShellProcessorException;
use DbManager\CoreBundle\Interfaces\DbDataManagerInterface;
use Symfony\Component\Process\Process;

/**
 * Mysql Processor instance
 */
abstract class AbstractDbCommands implements DbCommandsInterface
{
    public function __construct(
        protected readonly AppConfig $appConfig
    ) {
    }

    /**
     * @inheritdoc
     */
    public function create(DbDataManagerInterface $database): bool
    {
        $command = $this->getCreateCommandLine($database->getName());

        $this->runCommand($command);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function drop(DbDataManagerInterface $database): bool
    {
        $command = $this->getDropCommandLine($database->getName());

        $this->runCommand($command);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function dump(DbDataManagerInterface $database): string
    {
        $command = $this->getDumpCommandLine($database->getName(), '.');

        return $this->runCommand($command);
    }

    /**
     * @inheritdoc
     */
    public function import(DbDataManagerInterface $database): string
    {
        $command = $this->getImportCommandLine($database->getName(), $database->offsetGet('inputFile'));

        return $this->runCommand($command);
    }

    /**
     * Run shell command
     *
     * @param string $command
     *
     * @return string
     * @throws ShellProcessorException
     */
    protected function runCommand(string $command): string
    {
        $process = new Process([$command]);
        $process->setTimeout(null);

        $process->run();
        if (!$process->isSuccessful()) {
            throw new ShellProcessorException($process->getErrorOutput());
        }

        return $process->getOutput();
    }

    /**
     * Get dump command line
     *
     * @param string $dbName
     * @param string $outputPath
     *
     * @return string
     */
    protected function getDumpCommandLine(string $dbName, string $outputPath): string
    {
        return '';
    }

    /**
     * Get DB password
     *
     * @return string
     */
    protected function getPassword(): string
    {
        return $this->appConfig->getConfig('work_db_password')
            ? sprintf("-p%s", $this->appConfig->getConfig('work_db_password'))
            : '';
    }
}
