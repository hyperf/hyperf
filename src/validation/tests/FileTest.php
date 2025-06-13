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

namespace HyperfTest\Validation;

use HyperfTest\Validation\File\File;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing0
 * @coversNothing
 */
class FileTest extends TestCase
{
    public function testFile()
    {
        $file = File::create('foo.txt', 1024);
        $this->assertSame('text/plain', $file->getMimeType());
        $this->assertSame(1024 * 1024, $file->getSize());
        $this->assertSame(0, $file->getError());

        $file = File::createWithContent('foo.txt', 'bar');
        $this->assertSame('text/plain', $file->getMimeType());
        $this->assertSame(3, $file->getSize());
        $this->assertSame(0, $file->getError());
    }

    public function testImage()
    {
        $file = File::image('foo.png', 1024, 1024);
        $this->assertSame('image/png', $file->getMimeType());
        //  读取图片尺寸
        $imageSize = getimagesize($file->getPathname());
        $this->assertSame([1024, 1024], [$imageSize[0], $imageSize[1]]);
        $this->assertSame(0, $file->getError());
        $this->assertSame('png', $file->getExtension());

        $file = File::image('foo.jpg', 1024, 1024);
        $this->assertSame('image/jpeg', $file->getMimeType());
        //  读取图片尺寸
        $imageSize = getimagesize($file->getPathname());
        $this->assertSame([1024, 1024], [$imageSize[0], $imageSize[1]]);
        $this->assertSame(0, $file->getError());
        $this->assertSame('jpg', $file->getExtension());

        $file = File::image('foo.gif', 1024, 1024);
        $this->assertSame('image/gif', $file->getMimeType());
        //  读取图片尺寸
        $imageSize = getimagesize($file->getPathname());
        $this->assertSame([1024, 1024], [$imageSize[0], $imageSize[1]]);
        $this->assertSame(0, $file->getError());
        $this->assertSame('gif', $file->getExtension());
    }
}
