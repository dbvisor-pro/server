<?php

declare(strict_types=1);

namespace App\Service\Methods;

use \Exception;
readonly class MethodProcessor
{
    /**
     * @param MethodInterface[] $methods
     */
    public function __construct(
        private Iterable $methods = []
    ) {
    }

    /**
     * @param string $engine
     * @return array
     */
    public function getMethods(string $engine): array
    {
        $methods = [];
        foreach ($this->methods as $method) {
            if ($method->support($engine)) {
                $methods[$method->getCode()] = $method;
            }
        }
        return $methods;
    }

    /**
     * @param string $code
     * @return MethodInterface
     * @throws Exception
     */
    public function getMethodByCode(string $code): MethodInterface
    {
        foreach ($this->methods as $method) {
            if ($method->getCode() === $code) {
                return $method;
            }
        }

        throw new Exception(sprintf("No such method %s", $code));
    }
}
