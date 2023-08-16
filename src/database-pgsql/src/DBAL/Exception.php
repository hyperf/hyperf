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

use Doctrine\DBAL\Driver\Exception as DriverExceptionInterface;
use Exception as BaseException;
use Throwable;

class Exception extends BaseException implements DriverExceptionInterface
{
    /**
     * The SQLSTATE of the driver.
     */
    private ?string $sqlState;

    /**
     * @param string $message the driver error message
     * @param null|string $sqlState the SQLSTATE the driver is in at the time the error occurred, if any
     * @param int $code the driver specific error code if any
     * @param null|Throwable $previous the previous throwable used for the exception chaining
     */
    public function __construct($message, $sqlState = null, $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->sqlState = $sqlState;
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLState()
    {
        return $this->sqlState;
    }
}
