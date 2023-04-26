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
use Hyperf\Codec\Json;
use Hyperf\Contract\IPReaderInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Engine\Http\V2\Request;
use Hyperf\Grpc\Parser;
use Hyperf\Http2Client\Client;
use Hyperf\Nacos\Exception\ConnectToServerFailedException;
use Hyperf\Nacos\Protobuf\Any;
use Hyperf\Nacos\Protobuf\ListenContext;
use Hyperf\Nacos\Protobuf\ListenHandlerInterface;
use Hyperf\Nacos\Protobuf\Metadata;
use Hyperf\Nacos\Protobuf\Payload;
use Hyperf\Nacos\Protobuf\Request\ConfigBatchListenRequest;
use Hyperf\Nacos\Protobuf\Request\ConfigQueryRequest;
use Hyperf\Nacos\Protobuf\Request\ConnectionSetupRequest;
use Hyperf\Nacos\Protobuf\Request\HealthCheckRequest;
use Hyperf\Nacos\Protobuf\Request\RequestInterface;
use Hyperf\Nacos\Protobuf\Request\ServerCheckRequest;
use Hyperf\Nacos\Protobuf\Response\ConfigChangeBatchListenResponse;
use Hyperf\Nacos\Protobuf\Response\ConfigChangeNotifyRequest;
use Hyperf\Nacos\Protobuf\Response\ConfigQueryResponse;
use Hyperf\Nacos\Protobuf\Response\Response;
use Hyperf\Support\Network;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

use function Hyperf\Coroutine\go;

class GrpcClient
{
    protected array $listeners = [];

    protected ?Client $client = null;

    protected ?LoggerInterface $logger = null;

    /**
     * @var array<string, ListenContext>
     */
    protected array $configListenContexts = [];

    /**
     * @var array<string, ?ListenHandlerInterface>
     */
    protected array $configListenHandlers = [];

    protected int $streamId;

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

    public function listenConfig(string $group, string $dataId, ListenHandlerInterface $callback, string $md5 = '')
    {
        $listenContext = new ListenContext($this->namespaceId, $group, $dataId, $md5);
        $this->configListenContexts[$listenContext->toKeyString()] = $listenContext;
        $this->configListenHandlers[$listenContext->toKeyString()] = $callback;
    }

    public function listen(): void
    {
        $request = new ConfigBatchListenRequest(true, array_values($this->configListenContexts));
        $response = $this->request($request);
        if ($response instanceof ConfigChangeBatchListenResponse) {
            $changedConfigs = $response->changedConfigs;
            foreach ($changedConfigs as $changedConfig) {
                $this->handleConfig($changedConfig->tenant, $changedConfig->group, $changedConfig->dataId);
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
        $this->streamId = $this->bindStreamCall();
        $this->healthCheck();
    }

    protected function healthCheck()
    {
        go(function () {
            $client = $this->client;
            $heartbeat = $this->config->getGrpc()['heartbeat'];
            while ($heartbeat > 0) {
                if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield($heartbeat)) {
                    break;
                }
                $res = $this->request(new HealthCheckRequest(), $client);
                if ($res->errorCode !== 0) {
                    $this->logger?->error('Health check failed, the result is ' . (string) $res);
                }
            }
        });
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
                    $response = Response::jsonDeSerialize($response->getBody());
                    match (true) {
                        $response instanceof ConfigChangeNotifyRequest => $this->handleConfig(
                            $response->tenant,
                            $response->group,
                            $response->dataId,
                            $response
                        )
                    };

                    $this->listen();
                } catch (Throwable $e) {
                    $this->logger->error((string) $e);
                }
            }
        });

        $request = new ConnectionSetupRequest($this->namespaceId);
        $this->write($id, $request);
        sleep(1);

        return $id;
    }

    protected function handleConfig(string $tenant, string $group, string $dataId, ?ConfigChangeNotifyRequest $request = null)
    {
        $response = $this->request(new ConfigQueryRequest($tenant, $group, $dataId));
        $key = ListenContext::getKeyString($tenant, $group, $dataId);
        if ($response instanceof ConfigQueryResponse) {
            if (isset($this->configListenContexts[$key])) {
                $this->configListenContexts[$key]->md5 = $response->getMd5();
                $this->configListenHandlers[$key]?->handle($response);
            }

            if ($request && $ack = $this->configListenHandlers[$key]?->ack($request)) {
                $this->write($this->streamId, $ack);
            }
        }
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
}
