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

namespace Hyperf\Database;

use Hyperf\Stringable\Str;
use Throwable;

trait DetectsLostConnections
{
    /**
     * Determine if the given exception was caused by a lost connection.
     */
    protected function causedByLostConnection(Throwable $e): bool
    {
        $message = $e->getMessage();

        return Str::contains($message, [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'Error writing data to the connection',
            'Resource deadlock avoided',
            'Transaction() on null',
            'child connection forced to terminate due to client_idle_limit',
            'query_wait_timeout',
            'reset by peer',
            'Physical connection is not usable',
            'TCP Provider: Error code 0x68',
            'Name or service not known',
            'ORA-03114',
            'Packets out of order. Expected',
            'Broken pipe',
            'Error reading result',
            // PDO::prepare(): Send of 77 bytes failed with errno=110 Operation timed out
            // SSL: Handshake timed out
            // SSL: Operation timed out
            // SSL: Connection timed out
            // SQLSTATE[HY000] [2002] Connection timed out
            'timed out',
            // PDOStatement::execute(): Premature end of data
            'Premature end of data',
            'running with the --read-only option so it cannot execute this statement',
        ]);
    }
}
