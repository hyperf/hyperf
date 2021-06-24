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
namespace Hyperf\ConfigApollo;

use Hyperf\ConfigCenter\AbstractDriver;
use Psr\Container\ContainerInterface;

class ApolloDriver extends AbstractDriver
{
    /**
     * @var ClientInterface
     */
    protected $client;

    protected $driverName = 'apollo';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->client = $container->get(ClientInterface::class);
    }

    protected function pull(): array
    {
        return $this->client->pull($this->getNamespaces());
    }

    protected function formatValue($value)
    {
        if (! $this->config->get('config_center.drivers.apollo.strict_mode', false)) {
            return $value;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }

        if (is_numeric($value)) {
            $value = (strpos($value, '.') === false) ? (int) $value : (float) $value;
        }

        return $value;
    }

    protected function updateConfig(array $configs)
    {
        $mergedConfigs = [];
        foreach ($configs as $config) {
            foreach ($config as $key => $value) {
                $mergedConfigs[$key] = $value;
            }
        }
        unset($configs);
        foreach ($mergedConfigs ?? [] as $key => $value) {
            $this->config->set($key, $this->formatValue($value));
            $this->logger->debug(sprintf('Config [%s] is updated', $key));
        }
    }

    protected function getNamespaces(): array
    {
        return $this->config->get('config_center.drivers.apollo.namespaces', []);
    }
}
