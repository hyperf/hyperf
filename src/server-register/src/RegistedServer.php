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

namespace Hyperf\ServerRegister;

class RegistedServer
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $service;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var array
     */
    protected $meta;

    protected $raw;

    public function __construct(string $id, string $service, string $address, int $port, array $meta = [], $raw = null)
    {
        $this->id = $id;
        $this->service = $service;
        $this->address = $address;
        $this->port = $port;
        $this->meta = $meta;
        $this->raw = $raw;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getService(): string
    {
        return $this->service;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function getRaw()
    {
        return $this->raw;
    }
}
