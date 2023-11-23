<?php

declare(strict_types=1);

namespace DbManager\MagentoBundle\Service\Platform;

use App\Service\Platform\AbstractPlatform;

class Magento extends AbstractPlatform
{
    const CODE = 'magento';

    /**
     * @return string
     */
    public function getCode(): string
    {
        return self::CODE;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Magento (Adobe Commerce)';
    }
}
