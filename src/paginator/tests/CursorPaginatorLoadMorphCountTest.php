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

use Hyperf\Database\Model\Collection;
use Hyperf\Paginator\AbstractCursorPaginator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CursorPaginatorLoadMorphCountTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testCollectionLoadMorphCountCanChainOnThePaginator(): void
    {
        $relations = [
            'App\User' => 'photos',
            'App\Company' => ['employees', 'calendars'],
        ];

        $items = m::mock(Collection::class);
        $items->expects('loadMorphCount')->with('parentable', $relations);

        $p = (new class extends AbstractCursorPaginator {
            public function __toString()
            {
                return '';
            }
        })->setCollection($items);

        $this->assertSame($p, $p->loadMorphCount('parentable', $relations));
    }
}
