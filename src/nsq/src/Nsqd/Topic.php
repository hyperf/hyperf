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

class Topic extends AbstractEndpoint
{
    public function create(string $topic): bool
    {
        $response = $this->client->request('POST', '/topic/create', [
            RequestOptions::QUERY => [
                'topic' => $topic,
            ],
        ]);

        return $response->getStatusCode() === 200;
    }

    public function delete(string $topic): bool
    {
        $response = $this->client->request('POST', '/topic/delete', [
            RequestOptions::QUERY => [
                'topic' => $topic,
            ],
        ]);

        return $response->getStatusCode() === 200;
    }

    public function empty(string $topic): bool
    {
        $response = $this->client->request('POST', '/topic/empty', [
            RequestOptions::QUERY => [
                'topic' => $topic,
            ],
        ]);

        return $response->getStatusCode() === 200;
    }

    public function pause(string $topic): bool
    {
        $response = $this->client->request('POST', '/topic/pause', [
            RequestOptions::QUERY => [
                'topic' => $topic,
            ],
        ]);

        return $response->getStatusCode() === 200;
    }

    public function unpause(string $topic): bool
    {
        $response = $this->client->request('POST', '/topic/unpause', [
            RequestOptions::QUERY => [
                'topic' => $topic,
            ],
        ]);

        return $response->getStatusCode() === 200;
    }
}
