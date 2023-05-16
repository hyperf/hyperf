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
namespace Hyperf\Http2Client;

use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Engine\Channel;
use Hyperf\Engine\Contract\Http\V2\ClientInterface as HTTP2ClientInterface;
use Hyperf\Engine\Contract\Http\V2\RequestInterface as HTTP2RequestInterface;
use Hyperf\Engine\Contract\Http\V2\ResponseInterface as HTTP2ResponseInterface;
use Hyperf\Engine\Http\Stream;
use Hyperf\Engine\Http\V2\Client as HTTP2Client;
use Hyperf\Engine\Http\V2\Request;
use Hyperf\Http2Client\Exception\ClientClosedException;
use Hyperf\Http2Client\Exception\StreamLostException;
use Hyperf\Http2Client\Exception\TimeoutException;
use Hyperf\HttpMessage\Base\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class Client implements ClientInterface
{
    protected ?HTTP2ClientInterface $client = null;

    protected bool $loop = false;

    protected bool $heartbeat = false;

    protected Channel $wait;

    /**
     * @var array<int, null|Channel>
     */
    protected array $channels = [];

    protected ?LoggerInterface $logger = null;

    protected array $settings = [
        'timeout' => 10,
        'heartbeat' => 5,
        'retry_count' => 2,
    ];

    private string $identifier = Constants::WORKER_EXIT;

    public function __construct(protected string $baseUri, array $settings = [])
    {
        $this->wait = new Channel(1);
        $this->settings = array_replace($this->settings, $settings);
    }

    public function setLogger(?LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    public function send(HTTP2RequestInterface $request): int
    {
        $this->loop();

        if ($this->wait->isAvailable()) {
            $this->wait->pop($this->settings['timeout']);
            if ($this->wait->isTimeout()) {
                throw new TimeoutException('Connect timeout.');
            }
        }

        $streamId = $this->client->send($request);

        $this->channels[$streamId] = new Channel(1);

        return $streamId;
    }

    public function write(int $streamId, mixed $data, bool $end = false): bool
    {
        return $this->client->write($streamId, $data, $end);
    }

    public function recv(int $streamId, ?float $timeout = null): HTTP2ResponseInterface
    {
        $chan = $this->channels[$streamId] ?? null;
        if (! $chan) {
            throw new StreamLostException('The channel is lost.');
        }

        $response = $chan->pop($timeout ?? $this->settings['timeout']);
        if ($chan->isClosing()) {
            throw new ClientClosedException('Recv chan closed.');
        }

        if ($chan->isTimeout()) {
            throw new TimeoutException('Recv timeout.');
        }

        return $response;
    }

    public function closeChannel(int $streamId): void
    {
        if ($chan = $this->channels[$streamId] ?? null) {
            $chan->close();
            unset($this->channels[$streamId]);
        }
    }

    public function request(HTTP2RequestInterface $request): HTTP2ResponseInterface
    {
        $streamId = $this->send($request);

        try {
            return $this->recv($streamId);
        } finally {
            $this->closeChannel($streamId);
        }
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $headers = [];
        foreach ($request->getHeaders() as $name => $values) {
            $headers[$name] = implode(',', $values);
        }

        $response = $this->request(new Request(
            $request->getUri()->getPath(),
            $request->getMethod(),
            (string) $request->getBody(),
            $headers
        ));

        return (new Response())->withHeaders($response->getHeaders())
            ->withStatus($response->getStatusCode())
            ->withBody(new Stream($response->getBody()));
    }

    public function close()
    {
        $this->loop = false;
        $this->wait = new Channel(1);
        $client = $this->client;
        $this->client = null;
        $channels = $this->channels;
        $this->channels = [];

        if ($client?->isConnected()) {
            $client->close();
        }

        foreach ($channels as $channel) {
            $channel->close();
        }
    }

    public function loop(): void
    {
        if ($this->loop) {
            return;
        }

        $this->loop = true;

        $this->client = $this->makeClient();

        $this->heartbeat();

        Coroutine::create(function () {
            try {
                $client = $this->client;
                while (true) {
                    $response = $client->recv(-1);
                    if (! $client->isConnected()) {
                        throw new ClientClosedException('Read failed, because the http2 client is closed.');
                    }

                    $this->channels[$response->getStreamId()]?->push($response);
                }
            } catch (Throwable $throwable) {
                $this->logger?->error((string) $throwable);
            } finally {
                $this->close();
            }
        });

        $this->wait->close();
    }

    protected function makeClient(): HTTP2ClientInterface
    {
        $parsed = parse_url($this->baseUri);
        $scheme = $parsed['scheme'] ?? 'http';
        $ssl = $scheme === 'https';

        $host = $parsed['host'];
        $port = $parsed['port'] ?? ($ssl ? 443 : 80);

        return new HTTP2Client($host, $port, $ssl, $this->settings);
    }

    protected function getHeartbeat(): ?float
    {
        return $this->settings['heartbeat'] ?? null;
    }

    protected function heartbeat(): void
    {
        $heartbeat = $this->getHeartbeat();
        if (! $this->heartbeat && is_numeric($heartbeat)) {
            $this->heartbeat = true;

            go(function () use ($heartbeat) {
                try {
                    while (true) {
                        if (CoordinatorManager::until($this->identifier)->yield($heartbeat)) {
                            break;
                        }

                        try {
                            // PING
                            if (! $this->client?->ping()) {
                                $this->logger?->error('HTTP2 Client heartbeat failed.');
                                break;
                            }
                        } catch (Throwable $exception) {
                            $this->logger?->error((string) $exception);
                        }
                    }
                } finally {
                    $this->heartbeat = false;
                    $this->close();
                }
            });
        }
    }
}
