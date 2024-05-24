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
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class PgSQLTest extends AbstractTestCase
{
    #[RequiresPhpExtension('swoole', '< 6.0')]
    public function testExecute()
    {
        $res = DB::connection('pgsql')->execute('INSERT INTO public.users (email, name) VALUES (?, ?);', ['l@hyperf.io', 'limx']);

        $this->assertGreaterThan(0, $res);

        $res = DB::connection('pgsql')->fetch('SELECT * FROM public.users WHERE name = ? ORDER BY id DESC;', ['limx']);

        $this->assertSame('l@hyperf.io', $res['email']);
    }
}
