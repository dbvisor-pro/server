<?php

declare(strict_types=1);

namespace DbManager\CoreBundle\DbCommands;

use DbManager\CoreBundle\Exception\ShellProcessorException;
use DbManager\CoreBundle\Interfaces\DbDataManagerInterface;

interface DbCommandsInterface
{
    /**
     * Create DB
     *
     * @param DbDataManagerInterface $database
     *
     * @return bool
     */
    public function create(DbDataManagerInterface $database): bool;

    /**
     * Drop DB
     *
     * @param DbDataManagerInterface $database
     *
     * @return bool
     */
    public function drop(DbDataManagerInterface $database): bool;

    /**
     * @param DbDataManagerInterface $database
     *
     * @return string
     * @throws ShellProcessorException
     */
    public function dump(DbDataManagerInterface $database): string;

    /**
     * Import DB
     *
     * @param DbDataManagerInterface $database
     *
     * @return string
     * @throws ShellProcessorException
     */
    public function import(DbDataManagerInterface $database): string;
}