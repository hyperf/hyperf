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
namespace Hyperf\Elasticsearch;

use Elasticsearch\ClientBuilder;
use Hyperf\Guzzle\RingPHP\CoroutineHandler;
use Swoole\Coroutine;

class ClientBuilderFactory
{
    public function create()
    {
        $builder = ClientBuilder::create();
        if (Coroutine::getCid() > 0) {
            $builder->setHandler(new CoroutineHandler());
        }

        return $builder;
    }
}
