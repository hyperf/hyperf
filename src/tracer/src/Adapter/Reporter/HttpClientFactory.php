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

namespace Hyperf\Tracer\Adapter\Reporter;

use Closure;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Engine\Channel;
use Hyperf\Engine\Coroutine;
use Hyperf\Guzzle\ClientFactory;
use RuntimeException;
use Throwable;
use Zipkin\Reporters\Http\ClientFactory as ClientFactoryInterface;

class HttpClientFactory implements ClientFactoryInterface
{
    protected ?Channel $chan = null;

    protected int $channelSize = 65535;

    public function __construct(private ClientFactory $clientFactory)
    {
    }

    public function build(array $options): callable
    {
        $this->loop();

        return function (string $payload) use ($options): void {
            $chan = $this->chan;
            $clientFactory = $this->clientFactory;

            $chan->push(static function () use ($payload, $options, $clientFactory) {
                $url = $options['endpoint_url'];
                unset($options['endpoint_url']);
                $client = $clientFactory->create($options);
                $additionalHeaders = $options['headers'] ?? [];
                $requiredHeaders = [
                    'Content-Type' => 'application/json',
                    'Content-Length' => strlen($payload),
                    'b3' => '0',
                ];
                $headers = array_merge($additionalHeaders, $requiredHeaders);
                $response = $client->post($url, [
                    'body' => $payload,
                    'headers' => $headers,
                    // If 'no_aspect' option is true, then the HttpClientAspect will not modify the client options.
                    'no_aspect' => true,
                ]);
                $statusCode = $response->getStatusCode();
                if (! in_array($statusCode, [200, 202])) {
                    throw new RuntimeException(
                        sprintf('Reporting of spans failed, status code %d', $statusCode)
                    );
                }
            });

            if ($chan->isClosing()) {
                throw new RuntimeException('Connection closed.');
            }
        };
    }

    public function close(): void
    {
        $chan = $this->chan;
        $this->chan = null;

        $chan?->close();
    }

    protected function loop(): void
    {
        if ($this->chan != null) {
            return;
        }

        $this->chan = new Channel($this->channelSize);

        Coroutine::create(function () {
            while (true) {
                while (true) {
                    /** @var null|Closure $closure */
                    $closure = $this->chan?->pop();
                    if (! $closure) {
                        break 2;
                    }
                    try {
                        $closure();
                    } catch (Throwable) {
                        break;
                    } finally {
                        $closure = null;
                    }
                }
            }

            $this->close();
        });

        Coroutine::create(function () {
            if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield()) {
                $this->close();
            }
        });
    }
}
