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

namespace Hyperf\Queue\Driver;

use Hyperf\Queue\Exception\InvalidPackerException;
use Hyperf\Queue\Packer\PackerInterface;
use Hyperf\Queue\Packer\PhpSerializer;
use Psr\Container\ContainerInterface;

abstract class Driver implements DriverInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var PackerInterface
     */
    protected $packer;

    public function __construct(ContainerInterface $container, $config)
    {
        $this->container = $container;
        $this->packer = $container->get($config['packer'] ?? PhpSerializer::class);

        if (!$this->packer instanceof PackerInterface) {
            throw new InvalidPackerException(sprintf('[Error] %s is not a invalid packer.', $config['packer']));
        }
    }
}
