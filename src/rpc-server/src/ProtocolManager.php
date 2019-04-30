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
use Hyperf\Contract\PackerInterface;
use Hyperf\Utils\Arr;

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
        return Arr::get(static::$protocols, 'protocols.' . $name, []);
    }

    public function getPacker(string $name): PackerInterface
    {
        $packer = Arr::get(static::$protocols, 'protocols.' . $name . '.packer');
        if (! $packer instanceof PackerInterface) {
            throw new \InvalidArgumentException('Packer does not exist.');
        }
        return $packer;
    }
}
