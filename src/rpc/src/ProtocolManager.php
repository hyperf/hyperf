<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Rpc;

use Hyperf\Contract\ConfigInterface;
use InvalidArgumentException;

class ProtocolManager
{
    /**
     * @var \Hyperf\Contract\ConfigInterface
     */
    private $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function register(string $name, array $data)
    {
        return $this->config->set('protocols.' . $name, $data);
    }

    public function registerOrAppend(string $name, array $data)
    {
        $key = 'protocols.' . $name;
        return $this->config->set($key, array_merge($this->config->get($key, []), $data));
    }

    public function getProtocol(string $name): array
    {
        return $this->config->get('protocols.' . $name, []);
    }

    public function getPacker(string $name): string
    {
        $packer = $this->config->get('protocols.' . $name . '.packer');
        if (! is_string($packer)) {
            throw new InvalidArgumentException(sprintf('Packer %s is not exists.', $name));
        }
        return $packer;
    }

    public function getTransporter(string $name): string
    {
        $result = $this->config->get('protocols.' . $name . '.transporter');
        if (! is_string($result)) {
            throw new InvalidArgumentException(sprintf('Transporter %s is not exists.', $name));
        }
        return $result;
    }

    public function getPathGenerator(string $name): string
    {
        $result = $this->config->get('protocols.' . $name . '.path-generator');
        if (! is_string($result)) {
            throw new InvalidArgumentException(sprintf('Path Generator %s is not exists.', $name));
        }
        return $result;
    }
}
