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
namespace Hyperf\Cache\Driver;

use Hyperf\Cache\Exception\InvalidArgumentException;
use Hyperf\Codec\Packer\PhpSerializerPacker;
use Hyperf\Contract\PackerInterface;
use Hyperf\Support\Traits\InteractsWithTime;
use Psr\Container\ContainerInterface;

abstract class Driver implements DriverInterface
{
    use InteractsWithTime;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var PackerInterface
     */
    protected $packer;

    /**
     * @var string
     */
    protected $prefix;

    public function __construct(ContainerInterface $container, array $config)
    {
        $this->container = $container;
        $this->config = $config;
        $this->prefix = $config['prefix'] ?? 'cache:';

        $packerClass = $config['packer'] ?? PhpSerializerPacker::class;
        $this->packer = $container->get($packerClass);
    }

    public function getConnection(): mixed
    {
        throw new InvalidArgumentException('Cannot support method getConnection.');
    }

    protected function getCacheKey(string $key)
    {
        return $this->prefix . $key;
    }
}
