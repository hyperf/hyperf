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
namespace HyperfTest\Session;

use Hyperf\Session\Handler\FileHandler;
use Hyperf\Utils\Filesystem\Filesystem;
use Hyperf\Utils\Str;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class FileHandlerTest extends TestCase
{
    public function testReadAndWrite()
    {
        $handler = new FileHandler(new Filesystem(), $path = '/tmp', 10);

        // Useless methods of FileHandler.
        $this->assertTrue($handler->open('', ''));
        $this->assertTrue($handler->close('', ''));

        $id = Str::random(40);
        $data = [
            'int' => 1,
            'true' => true,
            'false' => false,
            'float' => 1.23,
            'string' => 'foo',
            0 => 1,
            'array' => [
                'int' => 1,
                'true' => true,
                'false' => false,
                'float' => 1.23,
                'string' => 'foo',
                0 => 1,
            ],
        ];
        $this->assertTrue($handler->write($id, serialize($data)));
        $this->assertSame($data, unserialize($handler->read($id)));
        $this->assertFileExists('/tmp/' . $id);
        $handler->destroy($id);
        $this->assertFileNotExists('/tmp/' . $id);
    }

    public function testReadNotExistsSessionId()
    {
        $handler = new FileHandler(new Filesystem(), $path = '/tmp', 10);
        $this->assertSame('', $handler->read('not-exist'));
    }

    public function testGc()
    {
        $handler = new FileHandler(new Filesystem(), $path = __DIR__ . '/runtime/session', 1);
        $id = Str::random(40);
        $handler->write($id, 'foo');
        sleep(1);
        $handler->gc(1);
        $this->assertFileNotExists($path . '/' . $id);
    }
}
