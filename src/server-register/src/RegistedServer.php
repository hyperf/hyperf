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
    protected $id;

    protected $service;

    protected $address;

    protected $port;

    protected $protocol;

    protected $raw;

    public function __construct($id, $service, $address, $port, $protocol, $raw)
    {
        $this->id = $id;
        $this->service = $service;
        $this->address = $address;
        $this->port = $port;
        $this->protocol = $protocol;
        $this->raw = $raw;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getService()
    {
        return $this->service;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getProtocol()
    {
        return $this->protocol;
    }

    public function getRaw()
    {
        return $this->raw;
    }
}
