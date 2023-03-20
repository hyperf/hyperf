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

use Exception;
use Hyperf\Contract\IPReaderInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Engine\Http\V2\Request;
use Hyperf\Grpc\Parser;
use Hyperf\Http2Client\Client;
use Hyperf\Nacos\Exception\ConnectToServerFailedException;
use Hyperf\Nacos\Protobuf\Any;
use Hyperf\Nacos\Protobuf\Metadata;
use Hyperf\Nacos\Protobuf\Payload;
use Hyperf\Nacos\Protobuf\Request\ConfigBatchListenRequest;
use Hyperf\Nacos\Protobuf\Request\ConfigQueryRequest;
use Hyperf\Nacos\Protobuf\Request\ConnectionSetupRequest;
use Hyperf\Nacos\Protobuf\Request\HealthCheckRequest;
use Hyperf\Nacos\Protobuf\Request\RequestInterface;
use Hyperf\Nacos\Protobuf\Request\ServerCheckRequest;
use Hyperf\Nacos\Protobuf\Response\ConfigChangeBatchListenResponse;
use Hyperf\Nacos\Protobuf\Response\ConfigQueryResponse;
use Hyperf\Nacos\Protobuf\Response\Response;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Network;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class GrpcClient
{
    protected array $listeners = [];

    protected ?Client $client = null;

    protected ?LoggerInterface $logger = null;

    public function __construct(
        protected Application $app,
        protected Config $config,
        protected ContainerInterface $container,
        protected string $namespaceId = ''
    ) {
        if ($this->container->has(StdoutLoggerInterface::class)) {
            $this->logger = $this->container->get(StdoutLoggerInterface::class);
        }

        $this->reconnect();
    }

    public function listen(string $key, callable $callable): void
    {
        $this->listeners[$key] = $callable;
    }

    public function listenService()
    {
        $metadata = new Metadata([
            'type' => 'SubscribeServiceRequest',
            'clientIp' => $this->ip(),
            'headers' => $this->defaultHeaders(),
        ]);

        $payload = new Payload([
            'metadata' => $metadata,
            'body' => new Any([
                'value' => Json::encode([
                    'namespace' => $this->namespaceId,
                    'module' => 'naming',
                    'subscribe' => true,
                    'serviceName' => 'test',
                    'groupName' => 'DEFAULT_GROUP',
                    'clusters' => 'DEFAULT',
                ]),
            ]),
        ]);

        $res = $this->client->request(new Request('/Request/request', 'POST', Parser::serializeMessage($payload), $this->grpcDefaultHeaders()));

        $this->app->service->create('test', [
            'groupName' => 'DEFAULT_GROUP',
            'namespaceId' => $this->namespaceId,
            'selector' => Json::encode(['type' => 'none']),
            'metadata' => [],
        ]);

        while (true) {
            sleep(3000);

            $optional = [
                'groupName' => 'DEFAULT_GROUP',
                'namespaceId' => $this->namespaceId,
                // 'ephemeral' => 'true',
            ];

            $optionalData = array_merge($optional, [
                'clusterName' => 'DEFAULT',
                'weight' => 99,
                'metadata' => '',
                'enabled' => 'true',
            ]);

            $port = rand(9500, 9999);
            $this->app->instance->register('127.0.0.1', $port, 'test', $optionalData);
            var_dump('registered');
        }
    }

    public function request(RequestInterface $request, ?Client $client = null): Response
    {
        $payload = new Payload([
            'metadata' => new Metadata([
                'type' => $request->getType(),
                'clientIp' => $this->ip(),
            ]),
            'body' => new Any([
                'value' => Json::encode($request->getValue()),
            ]),
        ]);

        $client ??= $this->client;

        $response = $client->request(
            new Request('/Request/request', 'POST', Parser::serializeMessage($payload), $this->grpcDefaultHeaders())
        );

        return Response::jsonDeSerialize($response->getBody());
    }

    public function write(int $streamId, RequestInterface $request, ?Client $client = null): bool
    {
        $payload = new Payload([
            'metadata' => new Metadata([
                'type' => $request->getType(),
                'clientIp' => $this->ip(),
            ]),
            'body' => new Any([
                'value' => Json::encode($request->getValue()),
            ]),
        ]);

        $client ??= $this->client;

        return $client->write($streamId, Parser::serializeMessage($payload));
    }

    public function healthCheck()
    {
        go(function () {
            $client = $this->client;
            while (true) {
                if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield(10)) {
                    break;
                }
                $res = $this->request(new HealthCheckRequest(), $client);
                if ($res->errorCode !== 0) {
                    $this->logger?->error('Health check failed, the result is ' . (string) $res);
                }
            }
        });
    }

    public function listenConfig(string $md5 = '')
    {
        sleep(5);
        $request = new ConfigBatchListenRequest(true, [
            [
                'tenant' => $this->namespaceId,
                'group' => 'DEFAULT_GROUP',
                'dataId' => 'test',
                'md5' => $md5,
            ],
        ]);

        $response = $this->request($request);
        if ($response instanceof ConfigChangeBatchListenResponse) {
            $changedConfigs = $response->getChangedConfigs();
            foreach ($changedConfigs as $changedConfig) {
                $queryRequest = new ConfigQueryRequest($changedConfig['tenant'], $changedConfig['group'], $changedConfig['dataId']);
                $response = $this->request($queryRequest);
                if ($response instanceof ConfigQueryResponse) {
                    $this->listenConfig($response->getMd5());
                }
            }
        }
    }

    protected function reconnect(): void
    {
        $this->client && $this->client->close();
        $this->client = new Client(
            $this->config->getHost() . ':' . ($this->config->getPort() + 1000),
            [
                'heartbeat' => null,
            ]
        );
        if ($this->logger) {
            $this->client->setLogger($this->logger);
        }

        $this->serverCheck();
        $this->bindStreamCall();
        $this->healthCheck();
        $this->listenConfig();
        // $this->listenService();
        // $this->test();
    }

    protected function ip(): string
    {
        if ($this->container->has(IPReaderInterface::class)) {
            return $this->container->get(IPReaderInterface::class)->read();
        }

        return Network::ip();
    }

    protected function bindStreamCall(): int
    {
        $id = $this->client->send(new Request('/BiRequestStream/requestBiStream', 'POST', '', $this->grpcDefaultHeaders(), true));

        go(function () use ($id) {
            $client = $this->client;
            while (true) {
                try {
                    $response = $client->recv($id, -1);
                    var_dump($response);
                } catch (Throwable $e) {
                    echo $e;
                }
            }
        });

        $request = new ConnectionSetupRequest($this->namespaceId);
        $this->write($id, $request);
        sleep(1);

        return $id;
    }

    protected function queryConfig()
    {
        $metadata = new Metadata([
            'type' => 'ConfigQueryRequest',
            'clientIp' => '127.0.0.1',
            'headers' => [
                'notify' => true,
            ],
        ]);

        $payload = new Payload([
            'metadata' => $metadata,
            'body' => new Any([
                'value' => Json::encode([
                    'tenant' => $this->namespaceId,
                    'group' => 'DEFAULT_GROUP',
                    'dataId' => 'test',
                    'module' => 'config',
                ]),
            ]),
        ]);

        $res = $this->client->request(new Request('/Request/request', 'POST', Parser::serializeMessage($payload), $this->grpcDefaultHeaders()));
    }

    protected function serverCheck(): bool
    {
        $request = new ServerCheckRequest();

        for ($i = 0; $i < 30; ++$i) {
            try {
                $response = $this->request($request);
                if ($response->errorCode !== 0) {
                    $this->logger?->error('Nacos check server failed.');
                    if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield(1)) {
                        break;
                    }
                    continue;
                }

                return true;
            } catch (Exception $exception) {
                $this->logger?->error((string) $exception);
            }
        }

        throw new ConnectToServerFailedException('the nacos server is not ready to work in 30 seconds, connect to server failed');
    }

    private function grpcDefaultHeaders(): array
    {
        return [
            'content-type' => 'application/grpc+proto',
            'te' => 'trailers',
            'user-agent' => 'Nacos-Hyperf-Client:v3.0',
        ];
    }

    private function defaultHeaders(): array
    {
        $time = (string) (time()*1000);
        return [
            'charset' => 'utf-8',
            'exConfigInfo' => 'true',
            'Client-RequestToken' => md5($time),
            'Client-RequestTS' => $time,
            'Timestamp' => $time,
            // 'Spas-Signature' => 'PZqVeU8aUslLyV6tkuAG6qgjLKI=',
            'Client-AppName' => '',
        ];
    }
}
