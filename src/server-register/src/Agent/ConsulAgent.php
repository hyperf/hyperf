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

    public function registerService(array $service): bool
    {
        $response = $this->consul->registerService($service);
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
            if (! isset($service['Service'], $service['Address'], $service['Port'], $service['Meta']['Protocol'])) {
                continue;
            }

            $result[] = new RegistedServer(
                $serviceId,
                $service['Service'],
                $service['Address'],
                $service['Port'],
                $service['Meta']['Protocol'],
                $service
            );
        }

        return $result;
    }
}
