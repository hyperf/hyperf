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

namespace HyperfTest\DbConnection\Stubs;

use Hyperf\Context\Context;
use Hyperf\Coroutine\Coroutine;
use PDO;

class PDOStub extends PDO
{
    public $dsn;

    public $username;

    public $passwd;

    public $options;

    public static $destruct = 0;

    public function __construct(string $dsn, string $username, string $passwd, array $options)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->passwd = $passwd;
        $this->options = $options;
    }

    public function __destruct()
    {
        $key = PDOStub::class . '::destruct';
        $count = Context::get($key, 0);
        var_dump(Coroutine::id() . ' ' . spl_object_hash($this));
        var_dump(Coroutine::id() . ' __destruct_' . $count);
        Context::set($key, $count + 1);
    }

    public function prepare($statement, $driver_options = null)
    {
        return new PDOStatementStubPHP8($statement);
    }

    public function exec($statement)
    {
    }
}
