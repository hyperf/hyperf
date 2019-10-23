<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Amqp;

class Params
{
    protected $insist = false;

    protected $loginMethod = 'AMQPLAIN';

    protected $loginResponse;

    protected $locale = 'en_US';

    protected $connectionTimeout = 3.0;

    protected $readWriteTimeout = 3.0;

    protected $context;

    protected $keepalive = false;

    protected $heartbeat = 0;

    public function __construct(array $data)
    {
        if (isset($data['insist'])) {
            $this->insist = $data['insist'];
        }

        if (isset($data['login_method'])) {
            $this->loginMethod = $data['login_method'];
        }

        if (isset($data['login_response'])) {
            $this->loginResponse = $data['login_response'];
        }

        if (isset($data['locale'])) {
            $this->locale = $data['locale'];
        }

        if (isset($data['connection_timeout'])) {
            $this->connectionTimeout = $data['connection_timeout'];
        }

        if (isset($data['read_write_timeout'])) {
            $this->readWriteTimeout = $data['read_write_timeout'];
        }

        if (isset($data['context'])) {
            $this->context = $data['context'];
        }

        if (isset($data['keepalive'])) {
            $this->keepalive = $data['keepalive'];
        }

        if (isset($data['heartbeat'])) {
            $this->heartbeat = $data['heartbeat'];
        }
    }

    public function isInsist(): bool
    {
        return $this->insist;
    }

    public function getLoginMethod(): string
    {
        return $this->loginMethod;
    }

    public function getLoginResponse()
    {
        return $this->loginResponse;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getConnectionTimeout(): float
    {
        return $this->connectionTimeout;
    }

    public function getReadWriteTimeout(): float
    {
        return $this->readWriteTimeout;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function isKeepalive(): bool
    {
        return $this->keepalive;
    }

    public function getHeartbeat(): int
    {
        return $this->heartbeat;
    }

    public function setInsist(bool $insist)
    {
        $this->insist = $insist;
    }

    public function setLoginMethod(string $loginMethod)
    {
        $this->loginMethod = $loginMethod;
    }

    public function setLoginResponse($loginResponse)
    {
        $this->loginResponse = $loginResponse;
    }

    public function setLocale(string $locale)
    {
        $this->locale = $locale;
    }

    public function setConnectionTimeout(float $connectionTimeout)
    {
        $this->connectionTimeout = $connectionTimeout;
    }

    public function setReadWriteTimeout(float $readWriteTimeout)
    {
        $this->readWriteTimeout = $readWriteTimeout;
    }

    public function setContext($context)
    {
        $this->context = $context;
    }

    public function setKeepalive(bool $keepalive)
    {
        $this->keepalive = $keepalive;
    }

    public function setHeartbeat($heartbeat)
    {
        $this->heartbeat = $heartbeat;
    }
}
