<?php

declare(strict_types=1);

namespace Hyperf\XxlJob;

class Config
{
    /**
     * @var bool
     */
    private $enable = false;

    /**
     * @var string
     */
    private $baseUri = 'http://127.0.0.1:8080';

    /**
     * @var string
     */
    private $accessToken = '';

    /**
     * @var string
     */
    private $serverUrlPrefix = '';

    /**
     * @var array
     */
    private $guzzleConfig = [
        'headers' => [
            'charset' => 'UTF-8',
        ],
        'timeout' => 10,
    ];

    /**
     * @var string
     */
    private $appName = '';

    /**
     * @var string
     */
    private $clientUrl = '';

    /**
     * @var int
     */
    private $heartbeat = 30;

    public function isEnable(): bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): void
    {
        $this->enable = $enable;
    }

    public function setBaseUri(string $baseUri): void
    {
        $this->baseUri = $baseUri;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function setServerUrlPrefix(string $serverUrlPrefix): void
    {
        $this->serverUrlPrefix = $serverUrlPrefix;
    }

    public function setGuzzleConfig(array $guzzleConfig): void
    {
        $this->guzzleConfig = $guzzleConfig;
    }

    public function getAppName(): string
    {
        return $this->appName;
    }

    public function setAppName(string $appName): void
    {
        $this->appName = $appName;
    }

    public function getClientUrl(): string
    {
        return $this->clientUrl;
    }

    public function setClientUrl(string $clientUrl): void
    {
        $this->clientUrl = $clientUrl;
    }

    /**
     * @return string
     */
    public function getHeartbeat(): int
    {
        return $this->heartbeat;
    }

    public function setHeartbeat(int $heartbeat): void
    {
        $this->heartbeat = $heartbeat;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getServerUrlPrefix(): string
    {
        return $this->serverUrlPrefix;
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    public function getGuzzleConfig(): array
    {
        return $this->guzzleConfig;
    }
}
