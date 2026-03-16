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

namespace HyperfTest\HttpMessage\Upload;

use Hyperf\HttpMessage\Upload\UploadedFile;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class UploadedFileTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetSize()
    {
        $file = new UploadedFile('', 10, 0);

        $this->assertSame(10, $file->getSize());
    }
}
