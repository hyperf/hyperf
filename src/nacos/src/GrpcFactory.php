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
namespace Hyperf\Nacos;

use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;

class GrpcFactory
{
    /**
     * @var array<string, GrpcClient>
     */
    protected array $clients = [];

    public function __construct(protected Application $app, protected Config $config)
    {
    }

    public function get(string $namespaceId): GrpcClient
    {
        if (isset($this->clients[$namespaceId])) {
            return $this->clients[$namespaceId];
        }

        return $this->clients[$namespaceId] = new GrpcClient($this->app, $this->config, $this->container(), $namespaceId);
    }

    private function container(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }
}
