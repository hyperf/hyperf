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

use Hyperf\Utils\Str;

class Option
{
    /**
     * @var string
     */
    private $server = '';

    /**
     * @var string
     */
    private $appid = '';

    /**
     * @var array
     */
    private $namespaces = [];

    /**
     * @var string
     */
    private $cluster = 'default';

    /**
     * @var string
     */
    private $clientIp = '127.0.0.1';

    /**
     * @var int
     */
    private $pullTimeout = 10;

    /**
     * @var int
     */
    private $intervalTimeout = 60;

    public function buildBaseUrl(): string
    {
        return implode('/', [
            $this->getServer(),
            'configs',
            $this->getAppid(),
            $this->getCluster(),
        ]) . '/';
    }

    public function buildCacheKey(string $namespace): string
    {
        return implode('+', [$this->getAppid() . $this->getCluster(), $namespace]);
    }

    public function getServer(): string
    {
        return $this->server;
    }

    public function setServer(string $server): self
    {
        if (! Str::startsWith($server, 'http://')) {
            $server = 'http://' . $server;
        }
        $this->server = $server;
        return $this;
    }

    public function getAppid(): string
    {
        return $this->appid;
    }

    public function setAppid(string $appid): self
    {
        $this->appid = $appid;
        return $this;
    }

    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    public function setNamespaces(array $namespaces): self
    {
        $this->namespaces = $namespaces;
        return $this;
    }

    public function getCluster(): string
    {
        return $this->cluster;
    }

    public function setCluster(string $cluster): self
    {
        $this->cluster = $cluster;
        return $this;
    }

    public function getClientIp(): string
    {
        return $this->clientIp;
    }

    public function setClientIp(string $clientIp): self
    {
        $this->clientIp = $clientIp;
        return $this;
    }

    public function getPullTimeout(): int
    {
        return $this->pullTimeout;
    }

    public function setPullTimeout(int $pullTimeout): self
    {
        $this->pullTimeout = $pullTimeout;
        return $this;
    }

    public function getIntervalTimeout(): int
    {
        return $this->intervalTimeout;
    }

    public function setIntervalTimeout(int $intervalTimeout): self
    {
        $this->intervalTimeout = $intervalTimeout;
        return $this;
    }
}
