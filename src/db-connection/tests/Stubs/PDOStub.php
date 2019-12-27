<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\DbConnection\Stubs;

class PDOStub extends \PDO
{
    public $dsn;

    public $username;

    public $passwd;

    public $options;

    public function __construct(string $dsn, string $username, string $passwd, array $options)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->passwd = $passwd;
        $this->options = $options;
    }

    public function prepare($statement, $driver_options = null)
    {
        return new PDOStatementStub($statement);
    }

    public function exec($statement)
    {
    }
}
