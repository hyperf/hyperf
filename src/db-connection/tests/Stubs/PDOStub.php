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
use PDOStatement;

if (PHP_VERSION_ID >= 80300) {
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

        public function prepare($statement, $driver_options = null): false|PDOStatement
        {
            return new PDOStatementStubPHP8($statement);
        }

        public function exec($statement): false|int
        {
            return 0;
        }
    }
} else {
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

        public function prepare($statement, $driver_options = null)
        {
            return new PDOStatementStubPHP8($statement);
        }

        public function exec($statement)
        {
            return 0;
        }
    }
}
