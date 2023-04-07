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
namespace Hyperf\Server;

class Port
{
    protected string $name = 'http';

    protected int $type = ServerInterface::SERVER_HTTP;

    protected string $host = '0.0.0.0';

    protected int $port = 9501;

    protected int $sockType = 0;

    protected array $callbacks = [];

    protected array $settings = [];

    protected ?Option $options = null;

    public static function build(array $config): static
    {
        $config = self::filter($config);

        $port = new static();
        isset($config['name']) && $port->setName($config['name']);
        isset($config['type']) && $port->setType($config['type']);
        isset($config['host']) && $port->setHost($config['host']);
        isset($config['port']) && $port->setPort($config['port']);
        isset($config['sock_type']) && $port->setSockType($config['sock_type']);
        isset($config['callbacks']) && $port->setCallbacks($config['callbacks']);
        isset($config['settings']) && $port->setSettings($config['settings']);
        isset($config['options']) && $port->setOptions(Option::make($config['options']));

        return $port;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): static
    {
        $this->host = $host;
        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): static
    {
        $this->port = $port;
        return $this;
    }

    public function getSockType(): int
    {
        return $this->sockType;
    }

    public function setSockType(int $sockType): static
    {
        $this->sockType = $sockType;
        return $this;
    }

    public function getCallbacks(): array
    {
        return $this->callbacks;
    }

    public function setCallbacks(array $callbacks): static
    {
        $this->callbacks = $callbacks;
        return $this;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings): static
    {
        $this->settings = $settings;
        return $this;
    }

    public function getOptions(): ?Option
    {
        return $this->options;
    }

    public function setOptions(Option $options): static
    {
        $this->options = $options;
        return $this;
    }

    private static function filter(array $config): array
    {
        if ((int) $config['type'] === ServerInterface::SERVER_BASE) {
            $default = [
                'open_http2_protocol' => false,
                'open_http_protocol' => false,
            ];

            $config['settings'] = array_merge($default, $config['settings'] ?? []);
        }

        return $config;
    }
}
