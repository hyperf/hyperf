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
namespace HyperfTest\CodeParser\Stub;

use HyperfTest\Di\Stub\Ignore;
use stdClass;

class FooEnumStruct
{
    public function __construct(public FooEnum $enum = FooEnum::DEFAULT)
    {
    }

    public function stdClass(object $id = new stdClass()): void
    {
    }

    public function class(Ignore $ignore = new Ignore()): void
    {
    }
}
