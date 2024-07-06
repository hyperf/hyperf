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

namespace HyperfTest\DB\Cases;

use Hyperf\DB\DB;
use PHPUnit\Framework\Attributes\CoversNothing;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
/**
 * @internal
 * @coversNothing
 */
class DBTest extends AbstractTestCase
{
    public function testDBConnection()
    {
        $container = $this->getContainer();
        $db = $container->get(DB::class);
        $db2 = DB::connection('pdo');

        $this->assertInstanceOf(DB::class, $db);
        $this->assertInstanceOf(DB::class, $db2);

        $ref = new ReflectionClass($db);
        $property = $ref->getProperty('poolName');
        $this->assertSame('default', $property->getValue($db));
        $this->assertSame('pdo', $property->getValue($db2));
    }
}
