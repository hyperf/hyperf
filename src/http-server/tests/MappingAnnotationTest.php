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
namespace HyperfTest\HttpServer;

use Hyperf\HttpServer\Annotation\RequestMapping;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class MappingAnnotationTest extends TestCase
{
    public function testRequestMapping()
    {
        $mapping = new RequestMapping([]);
        // Assert default methods
        $this->assertSame(['GET', 'POST'], $mapping->methods);
        $this->assertNull($mapping->path);

        // Normal case
        $mapping = new RequestMapping([
            'methods' => 'get,post,put',
            'path' => $path = '/foo',
        ]);
        $this->assertSame(['GET', 'POST', 'PUT'], $mapping->methods);
        $this->assertSame($path, $mapping->path);

        // The methods have space
        $mapping = new RequestMapping([
            'methods' => 'get, post,  put',
            'path' => $path,
        ]);
        $this->assertSame(['GET', 'POST', 'PUT'], $mapping->methods);
        $this->assertSame($path, $mapping->path);
    }

    public function testRequestMappingWithArrayMethods()
    {
        $mapping = new RequestMapping([
            'methods' => [
                'GET', 'POST ', 'put',
            ],
            'path' => $path = '/foo',
        ]);
        $this->assertSame(['GET', 'POST', 'PUT'], $mapping->methods);
        $this->assertSame($path, $mapping->path);
    }

    public function testRequestMappingBindMainProperty()
    {
        $mapping = new RequestMapping(['value' => '/foo']);
        $this->assertSame(['GET', 'POST'], $mapping->methods);
        $this->assertSame('/foo', $mapping->path);
    }
}
