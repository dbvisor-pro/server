<?php

declare(strict_types=1);

namespace App\ServiceApi;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class AppServiceClient
{
    /**
     * @var HttpClientInterface
     */
    protected HttpClientInterface $client;

    /**
     * @param HttpClientInterface $httpClient
     * @param KernelInterface $kernel
     * @param string $serviceUrl
     * @param bool $serviceSecure
     * @param string $environment
     */
    public function __construct(
        protected HttpClientInterface $httpClient,
        protected KernelInterface $kernel,
        protected string $serviceUrl = '',
        private bool $serviceSecure = true,
        private string $environment = 'prod'
    ) {
    }

    /**
     * @param string $method
     * @param string $action
     * @param array  $options
     *
     * @return ResponseInterface
     *
     * @throws TransportExceptionInterface
     */
    public function request(string $method, string $action, array $options = []): ResponseInterface
    {
        if ($this->kernel->getEnvironment() === 'dev') {
            $options['verify_peer'] = false;
            $options['verify_host'] = false;
        }

        return $this->httpClient->request($method, $this->getUrl($action), $options);
    }

    /**
     * @param string $action
     *
     * @return string
     */
    protected function getUrl(string $action): string
    {
        $url = str_replace(['https://', 'http://'], '', $this->serviceUrl);
        $protocol = !$this->serviceSecure && $this->environment === 'dev' ? 'http' : 'https';

        return sprintf("%s://%s/api/%s", $protocol, rtrim($url, '/'), $action);
    }
}
