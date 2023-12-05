<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;


class LockService
{
    const FILE_PATH = 'var/processing.flag';

    private Filesystem $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    public function lock(): void
    {
        $this->filesystem->touch(self::FILE_PATH);
    }

    public function unlock(): void
    {
        $this->filesystem->remove(self::FILE_PATH);
    }

    public function isLocked(): bool
    {
        return $this->filesystem->exists(self::FILE_PATH);
    }
}
