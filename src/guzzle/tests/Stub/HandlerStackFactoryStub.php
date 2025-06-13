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

use Hyperf\Guzzle\HandlerStackFactory;

class HandlerStackFactoryStub extends HandlerStackFactory
{
    public function __construct()
    {
        $this->usePoolHandler = false;
    }
}
