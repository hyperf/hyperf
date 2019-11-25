<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\ServerRegister\Agent;

use Hyperf\Consul\Agent;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Server\Server;
use Hyperf\ServerRegister\RegistedServer;

class ConsulAgent extends AbstractAgent
{
    /**
     * @var \Hyperf\Consul\AgentInterface
     */
    protected $consul;

    public function __construct(ConfigInterface $config, ClientFactory $factory)
    {
        $this->consul = new Agent(function () use ($config, $factory) {
            return $factory->create([
                'timeout' => 2,
                'base_uri' => $config->get('consul.uri', Agent::DEFAULT_URI),
            ]);
        });
    }

    public function registerService(RegistedServer $server): bool
    {
        $requestBody = [
            'Name' => $server->getService(),
            'ID' => $server->getId(),
            'Address' => $server->getAddress(),
            'Port' => $server->getPort(),
            'Meta' => (object) $server->getMeta()['meta'] ?? [],
        ];

        var_dump($server->getMeta()['meta'] ?? []);

        $requestBody['Check'] = $this->parseCheckParam($server);

        $response = $this->consul->registerService($requestBody);

        return $response->getStatusCode() === 200;
    }

    public function services(): ?array
    {
        $response = $this->consul->services();
        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $servers = $response->json();
        $result = [];
        foreach ($servers as $serviceId => $service) {
            if (! isset($service['Service'], $service['Address'], $service['Port'])) {
                continue;
            }

            $result[] = new RegistedServer(
                $serviceId,
                $service['Service'],
                $service['Address'],
                (int) $service['Port'],
                $service['Meta'] ?? [],
                $service
            );
        }

        return $result;
    }

    protected function parseCheckParam(RegistedServer $server): ?array
    {
        $meta = $server->getMeta();
        if ($result = $meta['check'] ?? null) {
            if (! isset($result['DeregisterCriticalServiceAfter'])) {
                $result['DeregisterCriticalServiceAfter'] = '90m';
            }

            if (! isset($result['Interval'])) {
                $result['Interval'] = '1s';
            }

            switch ($meta['protocol']) {
                case Server::SERVER_BASE:
                    if (! isset($result['TCP'])) {
                        $result['TCP'] = "{$server->getAddress()}:{$server->getPort()}";
                    }
                    break;
                case Server::SERVER_HTTP:
                default:
                    if (! isset($result['HTTP'])) {
                        $result['HTTP'] = "http://{$server->getAddress()}:{$server->getPort()}/";
                    }
                    break;
            }
        }

        return $result;
    }
}
