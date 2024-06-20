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

namespace Hyperf\Database\PgSQL\DBAL;

use Doctrine\DBAL\Driver\Result as ResultInterface;
use Swoole\Coroutine\PostgreSQLStatement;

final class Result implements ResultInterface
{
    public function __construct(private PostgreSQLStatement $result)
    {
    }

    public function fetchNumeric()
    {
        return $this->result->fetchArray(result_type: SW_PGSQL_NUM);
    }

    public function fetchAssociative()
    {
        return $this->result->fetchAssoc();
    }

    public function fetchOne()
    {
        $row = $this->fetchNumeric();
        if ($row === false) {
            return false;
        }

        return $row[0];
    }

    public function fetchAllNumeric(): array
    {
        return $this->result->fetchAll(SW_PGSQL_NUM);
    }

    public function fetchAllAssociative(): array
    {
        return $this->result->fetchAll(SW_PGSQL_ASSOC);
    }

    public function fetchFirstColumn(): array
    {
        $resultSet = $this->result->fetchAll(SW_PGSQL_NUM);
        if ($resultSet === false) {
            return [];
        }

        return array_map(fn ($row) => $row[0], $resultSet);
    }

    public function rowCount(): int
    {
        return (int) $this->result->affectedRows();
    }

    public function columnCount(): int
    {
        return (int) $this->result->fieldCount();
    }

    public function free(): void
    {
    }
}
