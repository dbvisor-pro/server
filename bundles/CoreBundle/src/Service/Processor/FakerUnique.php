<?php

declare(strict_types=1);

namespace DbManager\CoreBundle\Service\Processor;

class FakerUnique
{
    /**
     * TODO: this could be refactored into separate services for different methods
     *
     * @param string $generatedValue
     * @param string $method
     * @param array $collection
     * @return string
     */
    public function makeUnique(string $generatedValue, string $method, array $collection): string
    {
        if (in_array($method, ['email', 'safeEmail'])) {
            return $this->makeUniqueEmail($collection, $generatedValue);
        }

        return $generatedValue . '_' . count($collection);
    }

    /**
     * @param array $emails
     * @param string $newEmail
     * @return string
     */
    private function makeUniqueEmail(array $emails, string $newEmail): string
    {
        if (!in_array($newEmail, $emails)) {
            return $newEmail;
        }

        $count = 1;
        list($baseEmail, $domain) = explode('@', $newEmail);

        while (in_array("$baseEmail+$count@$domain", $emails)) {
            $count++;
        }

        return sprintf("%s+%d@%s", $baseEmail, $count, $domain);
    }
}
