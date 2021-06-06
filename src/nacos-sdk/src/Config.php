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
namespace Hyperf\NacosSdk;

class Config
{
    /**
     * @var string
     */
    protected $baseUri = 'http://127.0.0.1:8848';

    /**
     * @var null|string
     */
    protected $username;

    /**
     * @var null|string
     */
    protected $password;

    /**
     * @var array
     */
    protected $guzzleConfig = [
        'headers' => [
            'charset' => 'UTF-8',
        ],
    ];

    /**
     * @param $config = [
     *     'base_uri' => 'http://127.0.0.1:8848',
     *     'username' => null,
     *     'password' => null,
     *     'guzzle' => [],
     * ]
     */
    public function __construct(array $config = [])
    {
        isset($config['base_uri']) && $this->baseUri = (string) $config['base_uri'];
        isset($config['username']) && $this->username = (string) $config['username'];
        isset($config['password']) && $this->password = (string) $config['password'];
        isset($config['guzzle_config']) && $this->guzzleConfig = (array) $config['guzzle_config'];
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

    public function getGuzzleConfig(): array
    {
        return $this->guzzleConfig;
    }
}
