<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Server\Stub;

use Hyperf\Server\Listener\InitProcessTitleListener;
use Hyperf\Utils\Context;

class InitProcessTitleListenerStub extends InitProcessTitleListener
{
    public function setTitle($title)
    {
        Context::set('test.server.process.title', $this->prefix . $title);
    }
}
