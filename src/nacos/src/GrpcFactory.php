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

use Hyperf\Context\ApplicationContext;
use Hyperf\Nacos\Exception\InvalidArgumentException;
use Psr\Container\ContainerInterface;

class GrpcFactory
{
    /**
     * @var array<string, array<string, GrpcClient>>
     */
    protected array $clients = [];

    public function __construct(protected Application $app, protected Config $config)
    {
        if (! $this->config->getGrpc()['enable']) {
            throw new InvalidArgumentException('GRPC module is disable, please set `nacos.default.grpc.enable = true`.');
        }
    }

    public function get(string $namespaceId, Module|string $module = 'config'): GrpcClient
    {
        $module instanceof Module && $module = $module->value;

        if (isset($this->clients[$namespaceId][$module])) {
            return $this->clients[$namespaceId][$module];
        }

        return $this->clients[$namespaceId][$module] = new GrpcClient($this->app, $this->config, $this->container(), $namespaceId, $module);
    }

    /**
     * @return array<string, array<string, GrpcClient>> array<namespaceId, <module, GrpcClient>>
     */
    public function getClients(): array
    {
        return $this->clients;
    }

    /**
     * @param string $module config or naming
     * @return array<string, GrpcClient> array<namespaceId, GrpcClient>
     */
    public function moduleClients(Module|string $module): array
    {
        $module instanceof Module && $module = $module->value;

        $result = [];
        foreach ($this->clients as $namespaceId => $clients) {
            foreach ($clients as $key => $client) {
                if ($key === $module) {
                    $result[$namespaceId] = $client;
                }
            }
        }

        return $result;
    }

    private function container(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }
}
