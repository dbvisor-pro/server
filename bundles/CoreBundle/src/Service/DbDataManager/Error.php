<?php

declare(strict_types=1);

namespace DbManager\CoreBundle\Service\DbDataManager;

use DbManager\CoreBundle\Interfaces\ErrorInterface;
use DbManager\CoreBundle\Enums\ErrorSeverityEnum;

class Error implements ErrorInterface
{
    /**
     * @var string
     */
    private string $message;

    /**
     * @var int
     */
    private int $severity;

    /**
     * @param string $message
     * @param int $severity
     */
    public function __construct(string $message, int $severity = ErrorSeverityEnum::WARNING->value)
    {
        $this->message = $message;
        $this->severity = $severity;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return ErrorInterface
     */
    public function setMessage(string $message): ErrorInterface
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param int $severity
     * @return ErrorInterface
     */
    public function setSeverity(int $severity): ErrorInterface
    {
        $this->severity = $severity;
        return $this;
    }

    /**
     * @return int
     */
    public function getSeverity(): int
    {
        return $this->severity;
    }
}
