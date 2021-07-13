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

namespace HyperfTest\DistributedLock\Cases;

use Hyperf\DistributedLock\Annotation\Lock;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AnnotationTest extends TestCase
{
    public function testInitLock()
    {
        $annotation = new Lock([
            'mutex' => 'test',
            'ttl' => 10,
        ]);

        $this->assertSame('test', $annotation->mutex);
        $this->assertSame(10, $annotation->ttl);
    }
}
