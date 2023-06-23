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
namespace Hyperf\ConfigApollo;

use Hyperf\Stringable\Str;

class Option
{
    private string $server = '';

    private string $appid = '';

    private array $namespaces = [];

    private string $cluster = 'default';

    private string $clientIp = '127.0.0.1';

    private int $pullTimeout = 10;

    private int $intervalTimeout = 60;

    private string $secret = '';

    public function buildBaseUrl(): string
    {
        return implode('/', [
            $this->getServer(),
            'configs',
            $this->getAppid(),
            $this->getCluster(),
        ]) . '/';
    }

    public function buildLongPullingBaseUrl(): string
    {
        return implode('/', [
            $this->getServer(),
            'notifications',
            'v2',
        ]);
    }

    public function buildCacheKey(string $namespace): string
    {
        return implode('+', [$this->getAppid() . $this->getCluster(), $namespace]);
    }

    public function getServer(): string
    {
        return $this->server;
    }

    public function setServer(string $server): static
    {
        if (! Str::startsWith($server, ['http://', 'https://'])) {
            $server = 'http://' . $server;
        }
        $this->server = $server;
        return $this;
    }

    public function getAppid(): string
    {
        return $this->appid;
    }

    public function setAppid(string $appid): static
    {
        $this->appid = $appid;
        return $this;
    }

    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    public function setNamespaces(array $namespaces): static
    {
        $this->namespaces = $namespaces;
        return $this;
    }

    public function getCluster(): string
    {
        return $this->cluster;
    }

    public function setCluster(string $cluster): static
    {
        $this->cluster = $cluster;
        return $this;
    }

    public function getClientIp(): string
    {
        return $this->clientIp;
    }

    public function setClientIp(string $clientIp): static
    {
        $this->clientIp = $clientIp;
        return $this;
    }

    public function getPullTimeout(): int
    {
        return $this->pullTimeout;
    }

    public function setPullTimeout(int $pullTimeout): static
    {
        $this->pullTimeout = $pullTimeout;
        return $this;
    }

    public function getIntervalTimeout(): int
    {
        return $this->intervalTimeout;
    }

    public function setIntervalTimeout(int $intervalTimeout): static
    {
        $this->intervalTimeout = $intervalTimeout;
        return $this;
    }

    public function setSecret(string $secret): static
    {
        $this->secret = $secret;
        return $this;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }
}
