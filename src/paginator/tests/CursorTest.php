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

namespace HyperfTest\Paginator;

use Hyperf\Carbon\Carbon;
use Hyperf\Paginator\Cursor;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CursorTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCanEncodeAndDecodeSuccessfully(): void
    {
        $cursor = new Cursor([
            'id' => 422,
            'created_at' => Carbon::now()->toDateTimeString(),
        ], true);

        $this->assertEquals($cursor, Cursor::fromEncoded($cursor->encode()));
    }

    public function testCanGetParams(): void
    {
        $cursor = new Cursor([
            'id' => 422,
            'created_at' => ($now = Carbon::now()->toDateTimeString()),
        ], true);

        $this->assertEquals([$now, 422], $cursor->parameters(['created_at', 'id']));
    }

    public function testCanGetParam(): void
    {
        $cursor = new Cursor([
            'id' => 422,
            'created_at' => ($now = Carbon::now()->toDateTimeString()),
        ], true);

        $this->assertEquals($now, $cursor->parameter('created_at'));
    }
}
