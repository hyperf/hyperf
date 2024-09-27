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
use PDO;
use ReturnTypeWillChange;

class PDOStub extends PDO
{
    public $dsn;

    public $username;

    public $passwd;

    public $options;

    public function __construct($dsn, $username, $passwd, $options)
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
        Context::set($key, $count + 1);
    }

    #[ReturnTypeWillChange]
    public function prepare($statement, $driver_options = null): bool|PDOStatementStubPHP8
    {
        return new PDOStatementStubPHP8($statement);
    }

    #[ReturnTypeWillChange]
    public function exec($statement): bool|int
    {
        return 0;
    }
}
