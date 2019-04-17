<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Server;

class Port
{
    /**
     * @var string
     */
    protected $name = 'http';

    /**
     * @var int
     */
    protected $type = ServerInterface::SERVER_HTTP;

    /**
     * @var string
     */
    protected $host = '0.0.0.0';

    /**
     * @var int
     */
    protected $port = 9501;

    /**
     * @var int
     */
    protected $sockType = SWOOLE_SOCK_TCP;

    /**
     * @var array
     */
    protected $callbacks = [];

    public static function build(array $config)
    {
        $port = new static();
        isset($config['name']) && $port->setName($config['name']);
        isset($config['type']) && $port->setType($config['type']);
        isset($config['host']) && $port->setHost($config['host']);
        isset($config['port']) && $port->setPort($config['port']);
        isset($config['callbacks']) && $port->setCallbacks($config['callbacks']);

        return $port;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Port
     */
    public function setName(string $name): Port
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return Port
     */
    public function setType(int $type): Port
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return Port
     */
    public function setHost(string $host): Port
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     * @return Port
     */
    public function setPort(int $port): Port
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return int
     */
    public function getSockType(): int
    {
        return $this->sockType;
    }

    /**
     * @param int $sockType
     * @return Port
     */
    public function setSockType(int $sockType): Port
    {
        $this->sockType = $sockType;
        return $this;
    }

    /**
     * @return array
     */
    public function getCallbacks(): array
    {
        return $this->callbacks;
    }

    /**
     * @param array $callbacks
     * @return Port
     */
    public function setCallbacks(array $callbacks): Port
    {
        $this->callbacks = $callbacks;
        return $this;
    }
}
