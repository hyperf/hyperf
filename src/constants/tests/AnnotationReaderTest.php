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
use Hyperf\Constants\ConstantsCollector;
use HyperfTest\Constants\Stub\ErrorCodeStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AnnotationReaderTest extends TestCase
{
    protected function setUp()
    {
        $reader = new AnnotationReader();

        $ref = new \ReflectionClass(ErrorCodeStub::class);
        $classConstants = $ref->getReflectionConstants();

        $data = $reader->getAnnotations($classConstants);
        ConstantsCollector::set(ErrorCodeStub::class, $data);
    }

    public function testGetAnnotations()
    {
        $data = ConstantsCollector::get(ErrorCodeStub::class);

        $this->assertSame('Server Error!', $data[ErrorCodeStub::SERVER_ERROR]['message']);
        $this->assertSame('SHOW ECHO', $data[ErrorCodeStub::SHOW_ECHO]['message']);
        $this->assertSame('ECHO', $data[ErrorCodeStub::SHOW_ECHO]['echo']);

        $this->assertArrayNotHasKey(ErrorCodeStub::NO_MESSAGE, $data);
    }

    public function testGetMessageWithArguments()
    {
        $res = ErrorCodeStub::getMessage(ErrorCodeStub::PARAMS_INVALID);

        $this->assertSame('Params[%s] is invalid.', $res);

        $res = ErrorCodeStub::getMessage(ErrorCodeStub::PARAMS_INVALID, 'user_id');

        $this->assertSame('Params[user_id] is invalid.', $res);
    }
}
