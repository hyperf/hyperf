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
        ++self::$destruct;
    }

    public function prepare($statement, $driver_options = null)
    {
        if (version_compare(PHP_VERSION, '8.0', '>=')) {
            return new PDOStatementStubPHP8($statement);
        }
        return new PDOStatementStub($statement);
    }

    public function exec($statement)
    {
    }
}
