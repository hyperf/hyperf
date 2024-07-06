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

namespace HyperfTest\RpcServer\Stub;

class IdGeneratorStub
{
    /**
     * @var string
     */
    public $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public function generate(): string
    {
        return uniqid();
    }
}
