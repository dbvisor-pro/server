<?php

declare(strict_types=1);

namespace DbManager\CoreBundle\Interfaces;

interface ErrorInterface
{

    /**
     * Set error message
     *
     * @return string
     */
    public function getMessage(): string;

    /**
     * retrieve error message
     *
     * @param string $message
     * @return self
     */
    public function setMessage(string $message): self;

    /**
     * set severity of error. See ErrorEnum to get more information regarding different levels
     *
     * @param int $severity
     * @return self
     */
    public function setSeverity(int $severity): self;

    /**
     * Retrieve severity of error. See ErrorEnum to get more information regarding different levels
     *
     * @return int
     */
    public function getSeverity(): int;
}
