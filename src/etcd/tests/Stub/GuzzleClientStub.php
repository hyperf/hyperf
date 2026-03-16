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

namespace HyperfTest\Etcd\Stub;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;

class GuzzleClientStub extends Client
{
    public function request($method, $uri = '', array $options = []): ResponseInterface
    {
        if ($uri == 'kv/put') {
            $stream = fopen('php://temp', 'r+');
            fwrite($stream, '{"header":{"cluster_id":"11588568905070377092","member_id":"128088275939295631","revision":"10","raft_term":"3"}}');
            fseek($stream, 0);
            return new Response(200, [], new Stream($stream));
        }

        if ($uri == 'kv/range') {
            $stream = fopen('php://temp', 'r+');
            fwrite($stream, '{"header":{"cluster_id":"11588568905070377092","member_id":"128088275939295631","revision":"12","raft_term":"3"},"kvs":[{"key":"L3Rlc3QvdGVzdDI=","create_revision":"7","mod_revision":"12","version":"6","value":"SGVsbG8gV29ybGQh"}],"count":"1"}');
            fseek($stream, 0);
            return new Response(200, [], new Stream($stream));
        }

        return parent::request($method, $uri, $options);
    }
}
