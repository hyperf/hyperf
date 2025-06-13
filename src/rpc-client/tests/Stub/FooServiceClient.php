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

namespace HyperfTest\RpcClient\Stub;

use Hyperf\Contract\ConfigInterface;
use Hyperf\RpcClient\AbstractServiceClient;
use Psr\Container\ContainerInterface;

class FooServiceClient extends AbstractServiceClient
{
    protected string $serviceName = 'FooService';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $this->container->get(ConfigInterface::class);
    }

    public function createNodes(): array
    {
        return parent::createNodes();
    }
}
