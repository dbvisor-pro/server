<?php

declare(strict_types=1);

namespace App\Service\PublicCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand
{
    abstract public function execute(InputInterface $input, OutputInterface $output);
}
