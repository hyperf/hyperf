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
use Hyperf\Contract\IPReaderInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nacos\Application;
use Hyperf\Nacos\Config;
use Hyperf\Nacos\GrpcClient;
use Hyperf\Nacos\Protobuf\ListenHandler\ConfigChangeNotifyRequestHandler;
use Hyperf\Nacos\Protobuf\Response\ConfigQueryResponse;
use Psr\Container\ContainerInterface;

use function Hyperf\Coroutine\run;

require __DIR__ . '/../../../vendor/autoload.php';

run(function () {
    $container = Mockery::mock(ContainerInterface::class);
    $container->shouldReceive('has')->with(IPReaderInterface::class)->andReturnFalse();
    $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturnTrue();
    $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturnUsing(function () {
        $logger = Mockery::mock(StdoutLoggerInterface::class);
        $logger->shouldReceive('error')->andReturnUsing(function ($args) {
            var_dump($args);
        });
        return $logger;
    });
    $config = new Config([
        'host' => '127.0.0.1',
        'port' => 8848,
    ]);
    $client = new GrpcClient(new Application($config), $config, $container, '6ec44ac0-e12d-4841-a80a-94a0872559d9');
    $client->listenConfig('DEFAULT_GROUP', 'test', new ConfigChangeNotifyRequestHandler(static function (ConfigQueryResponse $request) {
        var_dump($request->getContent());
    }));
    $client->listen();
});
