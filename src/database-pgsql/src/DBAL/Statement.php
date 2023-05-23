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

use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;
use Swoole\Coroutine\PostgreSQLStatement;

use function ksort;

final class Statement implements StatementInterface
{
    private array $parameters = [];

    public function __construct(private PostgreSQLStatement $stmt)
    {
    }

    /** {@inheritdoc} */
    public function bindValue($param, $value, $type = ParameterType::STRING): bool
    {
        $this->parameters[$param] = $value;
        return true;
    }

    /** {@inheritdoc} */
    public function bindParam($param, &$variable, $type = ParameterType::STRING, $length = null): bool
    {
        $this->parameters[$param] = &$variable;
        return true;
    }

    /** {@inheritdoc} */
    public function execute($params = null): Result
    {
        if (! empty($params)) {
            foreach ($params as $param => $value) {
                if (is_int($param)) {
                    $this->bindValue($param + 1, $value, ParameterType::STRING);
                } else {
                    $this->bindValue($param, $value, ParameterType::STRING);
                }
            }
        }

        ksort($this->parameters);

        if (! $this->stmt->execute($this->parameters)) {
            throw new Exception($this->stmt->error ?? 'Execute failed.');
        }

        return new Result($this->stmt);
    }
}
