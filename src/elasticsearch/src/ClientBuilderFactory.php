<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
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
