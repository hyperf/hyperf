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

use Hyperf\Codec\Json;
use Hyperf\Context\Context;
use PDO;
use PDOStatement;
use ReturnTypeWillChange;

class PDOStub extends PDO
{
    public function __construct(public string $dsn, public ?string $username = null, public ?string $password = null, public ?array $options = null)
    {
    }

    public function __destruct()
    {
        $debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $data = [
            $debug[0] ?? null,
            $debug[1] ?? null,
            $debug[2] ?? null,
            $debug[3] ?? null,
            $debug[4] ?? null,
            $debug[5] ?? null,
            $debug[6] ?? null,
        ];
        var_dump(Json::encode($data));
        $key = PDOStub::class . '::destruct';
        $count = Context::get($key, 0);
        Context::set($key, $count + 1);
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
