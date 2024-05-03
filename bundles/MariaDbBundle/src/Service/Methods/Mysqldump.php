<?php

declare(strict_types=1);

namespace DbManager\MariaDbBundle\Service\Methods;

use DbManager\MariaDbBundle\Service\Engine\MariaDb as MariaDbEngine;
use DbManager\MysqlBundle\Service\Methods\Mysqldump as MySQLEngineDump;

class Mysqldump extends MySQLEngineDump
{
    /**
     * @inheritDoc
     */
    public function support(string $engine): bool
    {
        return $engine === MariaDbEngine::ENGINE_CODE;
    }
}
