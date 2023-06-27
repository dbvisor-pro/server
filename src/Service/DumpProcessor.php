<?php

declare(strict_types=1);

namespace App\Service;

use DbManager\CoreBundle\DbCommands\DbCommandsFactory;
use DbManager\CoreBundle\Exception\NoSuchEngineException;
use DbManager\CoreBundle\Exception\ShellProcessorException;
use DbManager\CoreBundle\Service\DbDataManager;

class DumpProcessor
{
    /**
     * @param DbCommandsFactory $dbCommandsFactory
     */
    public function __construct(
        private readonly DbCommandsFactory $dbCommandsFactory
    ) {
    }

    /**
     * @param string $tempDatabase
     * @param string $backupPath
     *
     * @return void
     * @throws ShellProcessorException
     * @throws NoSuchEngineException
     */
    public function dump(string $tempDatabase, string $backupPath = ''): void
    {
        $dbCommand = $this->dbCommandsFactory->create();
        $dbCommand->dump(
            new DbDataManager([
                'name' => $tempDatabase,
                'backupPath' => $backupPath
            ])
        );
    }
}
