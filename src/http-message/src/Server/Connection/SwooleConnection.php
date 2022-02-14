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
namespace Hyperf\HttpMessage\Server\Connection;

use Hyperf\HttpMessage\Server\Chunk\Chunkable;
use Hyperf\HttpMessage\Server\ConnectionInterface;
use Swoole\Http\Response;

class SwooleConnection implements ConnectionInterface, Chunkable
{
    /**
     * @var Response
     */
    protected $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function write(string $data): bool
    {
        return $this->response->write($data);
    }
}
