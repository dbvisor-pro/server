<?php

declare(strict_types=1);

namespace DbManager\CoreBundle\Service\Processor;

use Faker\Factory;
use Faker\Generator;

class Faker
{
    /**
     * @var Generator|null
     */
    protected ?Generator $faker = null;

    /**
     * @param FakerUnique $fakerUnique
     */
    public function __construct(
        private readonly FakerUnique $fakerUnique
    ) {
        $this->faker = Factory::create();
    }

    /**
     * @param string $method
     * @param array $options
     * @param int $count
     * @param bool $unique
     * @param int|null $maxLength
     * @return array
     */
    public function generateFakeCollection(
        string $method,
        array $options = [],
        int $count = 1,
        bool $unique = false,
        ?int $maxLength = null
    ): array {
        $collection = [];
        for ($i = 0; $i < $count; $i++) {
            $generatedValue = $this->generateFake($method, $options, $maxLength);
            if ($unique && in_array($generatedValue, $collection)) {
                $generatedValue = $this->fakerUnique->makeUnique($generatedValue, $method, $collection);
            }
            $collection[] = $generatedValue;
        }

        return $collection;
    }

    /**
     * @param string $method
     * @param array $options
     * @param int|null $maxLength
     * @return string
     */
    public function generateFake(string $method, array $options, ?int $maxLength = null): string
    {
        $value = (string)$this->faker->{$method}(...$options);
        if ($maxLength) {
            $value = mb_substr($value, 0, $maxLength);
        }

        return $value;
    }
}
