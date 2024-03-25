<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Nacos;

use JetBrains\PhpStorm\ArrayShape;

class Config
{
    protected string $baseUri = 'http://127.0.0.1:8848/';

    protected ?string $username = null;

    protected ?string $password = null;

    protected ?string $accessKey = null;

    protected ?string $accessSecret = null;

    protected string $host = '127.0.0.1';

    protected int $port = 8848;

    protected array $grpc = [
        'enable' => true,
        'heartbeat' => 10,
    ];

    protected array $guzzleConfig = [
        'headers' => [
            'charset' => 'UTF-8',
        ],
        'http_errors' => false,
    ];

    public function __construct(
        #[ArrayShape([
            'base_uri' => 'string',
            'username' => 'string',
            'password' => 'string',
            'access_key' => 'string',
            'access_secret' => 'string',
            'guzzle_config' => 'array',
            'host' => 'string',
            'port' => 'int',
        ])]
        array $config = []
    ) {
        isset($config['base_uri']) && $this->baseUri = (string) $config['base_uri'];
        isset($config['username']) && $this->username = (string) $config['username'];
        isset($config['password']) && $this->password = (string) $config['password'];
        isset($config['access_key']) && $this->accessKey = (string) $config['access_key'];
        isset($config['access_secret']) && $this->accessSecret = (string) $config['access_secret'];
        isset($config['guzzle_config']) && $this->guzzleConfig = (array) $config['guzzle_config'];
        isset($config['host']) && $this->host = (string) $config['host'];
        isset($config['port']) && $this->port = (int) $config['port'];
        isset($config['grpc']) && $this->grpc = array_replace($this->grpc, $config['grpc']);
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getAccessKey(): ?string
    {
        return $this->accessKey;
    }

    public function getAccessSecret(): ?string
    {
        return $this->accessSecret;
    }

    public function getGuzzleConfig(): array
    {
        return $this->guzzleConfig;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getGrpc(): array
    {
        return $this->grpc;
    }
}
