<?php

declare(strict_types=1);

namespace DbManager\CoreBundle\DataProcessor;

use DbManager\CoreBundle\DataProcessor\Platform\MagentoEavDataProcessorService;
use Illuminate\Database\Connection;

class DataProcessorFactory implements DataProcessorFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function create(
        string $tableName,
        array $rule,
        string $platform,
        Connection $connection
    ): DataProcessorInterface {
        if (isset($rule['eav']) && $rule['eav'] === true) {
            return match ($platform) {
                'magento' => new MagentoEavDataProcessorService($tableName, $rule, $connection)
            };
        }
        return new TableService($tableName, $rule, $connection);
    }
}