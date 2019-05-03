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

namespace Hyperf\RpcServer;

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

    public function getProtocol(string $name): array
    {
        return $this->config->get('protocols.' . $name, []);
    }

    public function getPacker(string $name): string
    {
        $result = $this->config->get('protocols.' . $name . '.packer');
        if (! is_string($result)) {
            throw new InvalidArgumentException(sprintf('Packer %s not exists.', $name));
        }
        return $result;
    }

    public function getTransporter(string $name): string
    {
        $result = $this->config->get('protocols.' . $name . '.transporter');
        if (! is_string($result)) {
            throw new InvalidArgumentException(sprintf('Transporter %s not exists.', $name));
        }
        return $result;
    }
}
