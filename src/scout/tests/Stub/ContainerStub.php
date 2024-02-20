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
namespace HyperfTest\Scout\Stub;

use Hyperf\Context\ApplicationContext;
use ReflectionClass;

class ContainerStub
{
    public static function unsetContainer(): void
    {
        $ref = new ReflectionClass(ApplicationContext::class);
        $c = $ref->getProperty('container');
        $c->setValue(null);
    }
}
