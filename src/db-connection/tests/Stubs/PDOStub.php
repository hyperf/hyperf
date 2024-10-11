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
use PDOStatement;
use ReturnTypeWillChange;

class PDOStub extends PDO
{
    public function __construct(public string $dsn, public ?string $username = null, public ?string $password = null, public ?array $options = null)
    {
    }

    #[ReturnTypeWillChange]
    public function prepare(string $query, array $options = []): bool|PDOStatement
    {
        return new PDOStatementStubPHP8($query);
    }

    #[ReturnTypeWillChange]
    public function exec(string $statement): bool|int
    {
        return 0;
    }
}
