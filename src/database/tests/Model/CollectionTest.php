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

namespace HyperfTest\Database\Model;

use Hyperf\Database\Model\Collection;
use HyperfTest\Database\Stubs\ModelStub;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class CollectionTest extends TestCase
{
    public function testMacroForCollection()
    {
        Collection::macro('getModelClassName', function () {
            if ($this->isEmpty()) {
                return null;
            }
            $item = $this->first();
            return get_class($item);
        });

        $this->assertNull(Collection::make()->getModelClassName());
        $this->assertSame(ModelStub::class, Collection::make([new ModelStub()])->getModelClassName());
        $this->assertInstanceOf(Collection::class, Collection::make());
    }
}
