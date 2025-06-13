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

namespace HyperfTest\Config\Stub;

class FooConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                'Foo' => function () {
                    return new Foo(1);
                },
                'Foo2' => [Foo::class, 'make'],
                'Foo3' => Foo::class,
            ],
        ];
    }
}
