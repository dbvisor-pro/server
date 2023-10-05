<?php

declare(strict_types=1);

namespace DbManager\TestBundle\Command;

use App\Exception\NoSuchMethodException;
use App\Service\PublicCommand\DatabaseDebugProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'app:db:process-debug',
    description: 'Start processing database by database id and temporary database name',
)]
final class ProcessDebugCommand extends Command
{
    /**
     * @param DatabaseDebugProcessor $databaseProcessor
     * @param LoggerInterface $logger
     * @param string|null $name
     */
    public function __construct(
        protected readonly DatabaseDebugProcessor $databaseProcessor,
        protected readonly LoggerInterface $logger,
        string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addOption(
            'uuid',
            null,
            InputOption::VALUE_REQUIRED,
            ''
        )->addOption(
            'db_name',
            null,
            InputOption::VALUE_OPTIONAL,
            ''
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->databaseProcessor->execute($input, $output);
        } catch (
            ClientExceptionInterface
            | RedirectionExceptionInterface
            | ServerExceptionInterface
            | DecodingExceptionInterface
            | NoSuchMethodException
            | TransportExceptionInterface $e
        ) {
            $this->logger->error($e->getMessage());

            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
