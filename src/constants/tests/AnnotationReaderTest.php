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

namespace HyperfTest\Constants;

use Hyperf\Constants\AnnotationReader;
use HyperfTest\Constants\Stub\ErrorCodeStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AnnotationReaderTest extends TestCase
{
    public function testGetAnnotations()
    {
        $reader = new AnnotationReader();

        $ref = new \ReflectionClass(ErrorCodeStub::class);
        $classConstants = $ref->getReflectionConstants();

        $res = $reader->getAnnotations($classConstants);

        $this->assertSame('Server Error!', $res[ErrorCodeStub::SERVER_ERROR]['message']);
        $this->assertSame('SHOW ECHO', $res[ErrorCodeStub::SHOW_ECHO]['message']);
        $this->assertSame('ECHO', $res[ErrorCodeStub::SHOW_ECHO]['echo']);

        $this->assertArrayNotHasKey(ErrorCodeStub::NO_MESSAGE, $res);
    }
}
