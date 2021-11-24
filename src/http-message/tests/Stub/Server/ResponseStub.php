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
namespace HyperfTest\HttpMessage\Stub\Server;

use Hyperf\HttpMessage\Server\ConnectionInterface;
use Hyperf\HttpMessage\Server\ResponseProxyTrait;
use Psr\Http\Message\ResponseInterface;

class ResponseStub implements ResponseInterface
{
    use ResponseProxyTrait;

    /**
     * @var ConnectionInterface
     */
    protected $connection;

    public function setConnection(ConnectionInterface $connection)
    {
        $this->connection = $connection;
        return $this;
    }
}
