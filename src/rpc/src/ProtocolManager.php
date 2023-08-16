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
namespace Hyperf\Rpc;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\RpcMultiplex\DataFormatter;
use Hyperf\RpcMultiplex\Packer\JsonPacker;
use Hyperf\RpcMultiplex\PathGenerator;
use Hyperf\RpcMultiplex\Transporter;
use Hyperf\Stringable\Str;
use InvalidArgumentException;

class ProtocolManager
{
    public function __construct(private ConfigInterface $config)
    {
    }

    /**
     * @param $data = [
     *     'packer' => JsonPacker::class,
     *     'transporter' => Transporter::class,
     *     'path-generator' => PathGenerator::class,
     *     'data-formatter' => DataFormatter::class,
     *     'normalizer' => JsonRpcNormalizer::class,
     * ]
     */
    public function register(string $name, array $data): void
    {
        $this->config->set('protocols.' . $name, $data);
    }

    public function registerOrAppend(string $name, array $data): void
    {
        $key = 'protocols.' . $name;
        $this->config->set($key, array_merge($this->config->get($key, []), $data));
    }

    public function getProtocol(string $name): array
    {
        return $this->config->get('protocols.' . $name, []);
    }

    public function getPacker(string $name): string
    {
        return $this->getTarget($name, 'packer');
    }

    public function getTransporter(string $name): string
    {
        return $this->getTarget($name, 'transporter');
    }

    public function getPathGenerator(string $name): string
    {
        return $this->getTarget($name, 'path-generator');
    }

    public function getDataFormatter(string $name): string
    {
        return $this->getTarget($name, 'data-formatter');
    }

    public function getNormalizer(string $name): string
    {
        return $this->getTarget($name, 'normalizer', NormalizerInterface::class);
    }

    private function getTarget(string $name, string $target, ?string $default = null): string
    {
        $result = $this->config->get('protocols.' . Str::lower($name) . '.' . Str::lower($target));
        if (! is_string($result)) {
            if ($default) {
                return $default;
            }

            throw new InvalidArgumentException(sprintf('%s is not exists.', Str::studly($target, ' ')));
        }
        return $result;
    }
}
