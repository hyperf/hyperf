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
namespace Hyperf\DbConnection\Listener;

use Hyperf\Contract\ContainerInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Register;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

class RegisterConnectionResolverListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event)
    {
        if ($this->container->has(ConnectionResolverInterface::class)) {
            Register::setConnectionResolver(
                $this->container->get(ConnectionResolverInterface::class)
            );
        }
    }
}
