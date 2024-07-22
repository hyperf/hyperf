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
use Hyperf\Coroutine\Coroutine;
use Hyperf\Guzzle\RingPHP\CoroutineHandler;

class ClientBuilderFactory
{
    public function create(): ClientBuilder
    {
        $builder = ClientBuilder::create();
        if (Coroutine::inCoroutine()) {
            $builder->setHandler(new CoroutineHandler());
        }

        return $builder;
    }
}
