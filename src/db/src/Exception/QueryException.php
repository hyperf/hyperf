<?php
declare(strict_types=1);

namespace Hyperf\DB\Exception;


use Throwable;

class QueryException extends \PDOException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}