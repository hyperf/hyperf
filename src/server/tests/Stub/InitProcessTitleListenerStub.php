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

namespace HyperfTest\Server\Stub;

use Hyperf\Context\Context;
use Hyperf\Server\Listener\InitProcessTitleListener;

class InitProcessTitleListenerStub extends InitProcessTitleListener
{
    public function setTitle(string $title)
    {
        if ($this->isSupportedOS()) {
            Context::set('test.server.process.title', $title);
        }
    }

    public function isSupportedOS(): bool
    {
        return parent::isSupportedOS();
    }
}
