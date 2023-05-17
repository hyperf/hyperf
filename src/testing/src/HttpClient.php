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
namespace Hyperf\Testing;

use GuzzleHttp\Client;
use Hyperf\Codec\Packer\JsonPacker;
use Hyperf\Collection\Arr;
use Hyperf\Contract\PackerInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Guzzle\CoroutineHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\UriInterface;

class HttpClient
{
    protected Client $client;

    protected PackerInterface $packer;

    public function __construct(protected ContainerInterface $container, PackerInterface $packer = null, $baseUri = 'http://127.0.0.1:9501')
    {
        $this->packer = $packer ?? new JsonPacker();
        $handler = null;
        if (Coroutine::inCoroutine()) {
            $handler = new CoroutineHandler();
        }
        $this->client = new Client([
            'base_uri' => $baseUri,
            'timeout' => 2,
            'handler' => $handler,
        ]);
    }

    public function get(string|UriInterface $uri, array $data = [], array $headers = [])
    {
        $response = $this->client->get($uri, [
            'headers' => $headers,
            'query' => $data,
        ]);
        return $this->packer->unpack((string) $response->getBody());
    }

    public function post(string|UriInterface $uri, array $data = [], array $headers = [])
    {
        $response = $this->client->post($uri, [
            'headers' => $headers,
            'form_params' => $data,
        ]);

        return $this->packer->unpack((string) $response->getBody());
    }

    public function put(string|UriInterface $uri, array $data = [], array $headers = [])
    {
        $response = $this->client->put($uri, [
            'headers' => $headers,
            'form_params' => $data,
        ]);

        return $this->packer->unpack((string) $response->getBody());
    }

    public function patch(string|UriInterface $uri, array $data = [], array $headers = [])
    {
        $response = $this->client->patch($uri, [
            'headers' => $headers,
            'form_params' => $data,
        ]);

        return $this->packer->unpack((string) $response->getBody());
    }

    public function json(string|UriInterface $uri, array $data = [], array $headers = [])
    {
        $headers['Content-Type'] = 'application/json';
        $response = $this->client->post($uri, [
            'json' => $data,
            'headers' => $headers,
        ]);

        return $this->packer->unpack((string) $response->getBody());
    }

    public function file(string|UriInterface $uri, array $data = [], array $headers = [])
    {
        $multipart = [];
        if (Arr::isAssoc($data)) {
            $data = [$data];
        }

        foreach ($data as $item) {
            $name = $item['name'];
            $file = $item['file'];

            $multipart[] = [
                'name' => $name,
                'contents' => fopen($file, 'r'),
                'filename' => basename($file),
            ];
        }

        $response = $this->client->post($uri, [
            'headers' => $headers,
            'multipart' => $multipart,
        ]);

        return $this->packer->unpack((string) $response->getBody());
    }

    public function client(): Client
    {
        return $this->client;
    }
}
