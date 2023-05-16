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
namespace Cases\PgSQL;

use Hyperf\DB\DB;
use HyperfTest\DB\Cases\AbstractTestCase;

/**
 * @internal
 * @coversNothing
 */
class PgSQLTest extends AbstractTestCase
{
    public function setUp(): void
    {
        if (SWOOLE_MAJOR_VERSION < 5) {
            $this->markTestSkipped('PostgreSql requires Swoole version >= 5.0.0');
        }
    }

    public function testInsertAndGetId()
    {
        $res = DB::connection('pgsql')->insert('INSERT INTO public.users (email, name) VALUES (?, ?);', ['l@hyperf.io', 'limx']);

        $this->assertGreaterThan(0, $res);
    }
}
