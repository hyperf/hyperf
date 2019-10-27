<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Cache\Cases;

use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CachePut;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AnnotationTest extends TestCase
{
    public function testIntCacheableAndCachePut()
    {
        $annotation = new Cacheable([
            'prefix' => 'test',
            'ttl' => 3600,
        ]);

        $this->assertSame('test', $annotation->prefix);
        $this->assertSame(3600, $annotation->ttl);

        $annotation = new Cacheable([
            'prefix' => 'test',
            'ttl' => '3600',
        ]);

        $this->assertSame('test', $annotation->prefix);
        $this->assertSame(3600, $annotation->ttl);

        $annotation = new CachePut([
            'prefix' => 'test',
            'ttl' => '3600',
        ]);

        $this->assertSame('test', $annotation->prefix);
        $this->assertSame(3600, $annotation->ttl);
    }
}
