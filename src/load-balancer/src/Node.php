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
namespace Hyperf\LoadBalancer;

class Node
{
    /**
     * @var int
     */
    public $weight;

    /**
     * @var string
     */
    public $host;

    /**
     * @var int
     */
    public $port;

    /**
     * The path prefix, only support protocol `jsonrpc-http`.
     * @var string
     */
    public $pathPrefix = '';

    public function __construct(string $host, int $port, int $weight = 0, string $pathPrefix = '')
    {
        $this->host = $host;
        $this->port = $port;
        $this->weight = $weight;
        $this->pathPrefix = $pathPrefix;
    }
}
