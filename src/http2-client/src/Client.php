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
use Hyperf\Engine\Channel;
use Hyperf\Engine\Contract\Http\V2\ClientInterface as HTTP2ClientInterface;
use Hyperf\Engine\Contract\Http\V2\RequestInterface as HTTP2RequestInterface;
use Hyperf\Engine\Contract\Http\V2\ResponseInterface as HTTP2ResponseInterface;
use Hyperf\Engine\Http\Stream;
use Hyperf\Engine\Http\V2\Client as HTTP2Client;
use Hyperf\Engine\Http\V2\Request;
use Hyperf\Http2Client\Exception\ClientClosedException;
use Hyperf\Http2Client\Exception\TimeoutException;
use Hyperf\HttpMessage\Base\Response;
use Hyperf\Utils\Coroutine;
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

    protected Channel $chan;

    /**
     * @var Channel[]
     */
    protected array $channels = [];

    protected ?LoggerInterface $logger = null;

    protected array $settings = [
        'timeout' => 10,
        'heartbeat' => 5,
        'retry_count' => 2,
    ];

    public function __construct(protected string $baseUri, array $settings = [])
    {
        $this->chan = new Channel(65535);
        $this->settings = array_replace($this->settings, $settings);
    }

    public function setLogger(?LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    public function request(HTTP2RequestInterface $request): HTTP2ResponseInterface
    {
        $this->loop();

        if (! $this->client?->isConnected()) {
            // Wait the client connect to server.
            usleep(1000);
        }

        $streamId = $this->client->send($request);

        try {
            $this->channels[$streamId] = $chan = new Channel(1);

            $response = $chan->pop($this->settings['timeout']);
            if ($chan->isClosing()) {
                throw new ClientClosedException('Recv chan closed.');
            }

            if ($chan->isTimeout()) {
                throw new TimeoutException('Recv timeout.');
            }

            return $response;
        } finally {
            unset($this->channels[$streamId]);
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
        $client = $this->client;
        $this->client = null;
        if ($client?->isConnected()) {
            $client->close();
        }

        $channels = $this->channels;
        $this->channels = [];
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
                while (true) {
                    $client = $this->client;
                    $response = $client->recv(-1);
                    if (! $client->isConnected()) {
                        throw new ClientClosedException('Read failed, because the http2 client is closed.');
                    }

                    $this->channels[$response->getStreamId()]?->push($response);
                }
            } finally {
                $this->close();
            }
        });
    }

    protected function makeClient(): HTTP2ClientInterface
    {
        $parsed = parse_url($this->baseUri);
        $scheme = $parsed['scheme'] ?? 'http';
        $ssl = $scheme === 'https';

        $host = $parsed['host'];
        $port = $parsed['port'] ?? ($ssl ? 443 : 80);

        return new HTTP2Client($host, $port, $ssl);
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
                        if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield($heartbeat)) {
                            break;
                        }

                        try {
                            // PING
                            $this->client?->ping();

                            if ($this->client && ! $this->client->isConnected()) {
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
