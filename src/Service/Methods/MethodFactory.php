<?php

declare(strict_types=1);

namespace App\Service\Methods;

class MethodFactory
{
    /**
     * @param MethodProcessor $methodProcessor
     */
    public function __construct(
        private readonly MethodProcessor $methodProcessor
    ) {
    }

    /**
     * @param string $method
     * @return MethodInterface
     * @throws \Exception
     */
    public function create(string $method): MethodInterface
    {
        return $this->methodProcessor->getMethodByCode($method);
    }
}
