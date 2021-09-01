<?php

declare(strict_types=1);

namespace Hyperf\XxlJob;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class ApplicationFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $xxlJobConfig = $container->get(ConfigInterface::class)->get('xxl_job', []);
        $config = new Config();
        $config->setEnable($xxlJobConfig['enable'] ?? false);
        $config->setAppName($xxlJobConfig['app_name'] ?? '');
        $config->setAccessToken($xxlJobConfig['access_token'] ?? '');
        $adminAddressArr = parse_url($xxlJobConfig['admin_address'] ?? 'http://127.0.0.1:8769/xxl-job-admin');
        $config->setBaseUri(sprintf('%s://%s:%s', $adminAddressArr['scheme'], $adminAddressArr['host'], $adminAddressArr['port']));
        $config->setServerUrlPrefix($adminAddressArr['path'] ?? '');
        $config->setHeartbeat($xxlJobConfig['heartbeat'] ?? 30);
        if (isset($xxlJobConfig['guzzle']['config']) && ! empty($xxlJobConfig['guzzle']['config'])) {
            $config->setGuzzleConfig($xxlJobConfig['guzzle']['config']);
        }
        return new Application($config);
    }
}
