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

namespace HyperfTest\Guzzle\Stub;

use Hyperf\Engine\Http\Client;
use Hyperf\Guzzle\PoolHandler;

class PoolHandlerStub extends PoolHandler
{
    public $count = 0;

    protected function makeClient(string $host, int $port, bool $ssl): Client
    {
        ++$this->count;
        return parent::makeClient($host, $port, $ssl);
    }
}
