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

namespace Hyperf\Nsq\Nsqd;

use GuzzleHttp\RequestOptions;

class Channel extends AbstractEndpoint
{
    public function create(string $topic, string $channel): bool
    {
        $response = $this->client->request('POST', '/channel/create', [
            RequestOptions::QUERY => [
                'topic' => $topic,
                'channel' => $channel,
            ],
        ]);

        return $response->getStatusCode() === 200;
    }

    public function delete(string $topic, string $channel): bool
    {
        $response = $this->client->request('POST', '/channel/delete', [
            RequestOptions::QUERY => [
                'topic' => $topic,
                'channel' => $channel,
            ],
        ]);

        return $response->getStatusCode() === 200;
    }

    public function empty(string $topic, string $channel): bool
    {
        $response = $this->client->request('POST', '/channel/empty', [
            RequestOptions::QUERY => [
                'topic' => $topic,
                'channel' => $channel,
            ],
        ]);

        return $response->getStatusCode() === 200;
    }

    public function pause(string $topic, string $channel): bool
    {
        $response = $this->client->request('POST', '/channel/pause', [
            RequestOptions::QUERY => [
                'topic' => $topic,
                'channel' => $channel,
            ],
        ]);

        return $response->getStatusCode() === 200;
    }

    public function unpause(string $topic, string $channel): bool
    {
        $response = $this->client->request('POST', '/channel/unpause', [
            RequestOptions::QUERY => [
                'topic' => $topic,
                'channel' => $channel,
            ],
        ]);

        return $response->getStatusCode() === 200;
    }
}
