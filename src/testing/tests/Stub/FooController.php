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
namespace HyperfTest\Testing\Stub;

use Hyperf\Utils\Coroutine;

class FooController
{
    public function index()
    {
        return ['code' => 0, 'data' => 'Hello Hyperf!'];
    }

    public function exception()
    {
        throw new \RuntimeException('Server Error', 500);
    }

    public function id()
    {
        return ['code' => 0, 'data' => Coroutine::id()];
    }
}
