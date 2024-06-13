<?php

declare(strict_types=1);

namespace App\Service\PublicCommand\Server;

use App\Service\AppConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\PublicCommand\AbstractCommand;
use App\ServiceApi\Entity\Server;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class Ping extends AbstractCommand
{
    /**
     * @param AppConfig $appConfig
     * @param Server $serverApi
     */
    public function __construct(
        protected readonly AppConfig $appConfig,
        protected readonly Server $serverApi
    ) {
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): bool
    {
        $this->initInputOutput($input, $output);
        try {
            $this->serverApi->update(
                $this->appConfig->getServerUuid(),
                [
                    'pingDate' => (new \DateTime())->format('Y-m-d H:i:s')
                ]
            );

            return true;
        } catch (
            Exception
            | ClientExceptionInterface
            | DecodingExceptionInterface
            | ServerExceptionInterface
            | TransportExceptionInterface
            | InvalidArgumentException
            | RedirectionExceptionInterface $e
        ) {
            $this->getInputOutput()->error("During updating server an error happened: " . $e->getMessage());

            return false;
        }
    }
}
