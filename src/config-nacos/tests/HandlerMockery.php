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
namespace HyperfTest\ConfigNacos;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;

class HandlerMockery
{
    public function __invoke(RequestInterface $request, array $options)
    {
        $uri = $request->getUri()->getPath();
        $mapping = [
            '/nacos/v1/auth/users/login' => '/json/login.json',
            '/nacos/v1/ns/instance/list' => '/json/instance_list.json',
            '/nacos/v1/ns/instance' => '/json/instance_detail.json',
            '/nacos/v1/ns/operator/switches' => '/json/get_switches.json',
            '/nacos/v1/ns/operator/metrics' => '/json/get_metrics.json',
            '/nacos/v1/ns/operator/servers' => '/json/get_servers.json',
            '/nacos/v1/ns/raft/leader' => '/json/get_leader.json',
            '/nacos/v1/ns/service' => '/json/service_detail.json',
            '/nacos/v1/ns/service/list' => '/json/service_list.json',
        ];

        if ($json = $mapping[$uri] ?? null) {
            return new FulfilledPromise(new Psr7\Response(
                200,
                [],
                file_get_contents(__DIR__ . $json)
            ));
        }

        $data = '';
        switch ($uri) {
            case '/nacos/v1/cs/configs':
                $query = $request->getUri()->getQuery();
                $data = match (static::parse($query)['dataId']) {
                    'json' => '{"id": 1}',
                    'json2' => '{"ids": [1,2,3]}',
                    'text' => 'Hello World',
                    default => '{}',
                };
        }

        return new FulfilledPromise(new Psr7\Response(
            200,
            [],
            $data
        ));
    }

    public static function parse(string $query): array
    {
        $data = explode('&', $query);
        $result = [];
        foreach ($data as $item) {
            [$key, $value] = explode('=', $item);
            $result[$key] = $value;
        }
        return $result;
    }
}
