<?php

declare(strict_types=1);

namespace App\ServiceApi\Actions;

use App\ServiceApi\AppService;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class GetAccessToken extends AppService
{
    protected string $action = 'login_check';

    /**
     * Get Security Token
     *
     * @param string $username
     * @param string $password
     *
     * @return string
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function execute(string $username, string $password): string
    {
        $result = $this->getToken($username, $password);

        return $result['token'];
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return array
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function getToken(string $username, string $password): array
    {
        return $this->sendRequest(
            [
                'query' => [
                    'username' => $username,
                    'password' => $password
                ]
            ]
        );
    }

    /**
     * @inheritDoc
     */
    protected function getHeaders(): array
    {
        $headers = parent::getHeaders();
        if (isset($headers['Authorization'])) {
            unset($headers['Authorization']);
        }

        return $headers;
    }
}
