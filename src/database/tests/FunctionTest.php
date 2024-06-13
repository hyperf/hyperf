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

namespace HyperfTest\Database;

use PHPUnit\Framework\TestCase;

use function Hyperf\Database\Model\database_path;

/**
 * @internal
 * @coversNothing
 */
class FunctionTest extends TestCase
{
    public function testDatabasePath()
    {
        $this->assertSame(BASE_PATH . '/database/', database_path());
        $this->assertSame(BASE_PATH . '/database/foo', database_path('foo'));
    }
}
