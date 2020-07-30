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

use Hyperf\Paginator\LengthAwarePaginator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class LengthAwarePaginatorTest extends TestCase
{
    public function testNextPageUrl()
    {
        $paginator = new LengthAwarePaginator([1, 2], 10, 2);

        $this->assertSame('/?page=2', $paginator->nextPageUrl());

        $paginator = new LengthAwarePaginator([1, 2], 10, 2, 5);

        $this->assertSame(null, $paginator->nextPageUrl());
    }

    public function testFirstItem()
    {
        $paginator = new LengthAwarePaginator([1, 2], 10, 2, 2);

        $this->assertSame(3, $paginator->firstItem());
        $this->assertSame(4, $paginator->lastItem());
    }

    public function testAppends()
    {
        $paginator = new LengthAwarePaginator([1, 2], 10, 2, 2);
        $paginator = $paginator->appends('keyword', 'Hyperf');
        $this->assertSame('/?keyword=Hyperf&page=1', $paginator->url(1));
    }
}
