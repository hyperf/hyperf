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

namespace HyperfTest\Session\Stub;

use SessionHandlerInterface;

class FooHandler implements SessionHandlerInterface
{
    /**
     * Close the session.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.close.php
     * @return bool <p>
     *                  The return value (usually TRUE on success, FALSE on failure).
     *                  Note this value is returned internally to PHP for processing.
     *                  </p>
     * @since 5.4.0
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Destroy a session.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @return bool <p>
     *                  The return value (usually TRUE on success, FALSE on failure).
     *                  Note this value is returned internally to PHP for processing.
     *                  </p>
     * @since 5.4.0
     */
    public function destroy(string $id): bool
    {
        return true;
    }

    /**
     * Cleanup old sessions.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.gc.php
     * @return bool <p>
     *                  The return value (usually TRUE on success, FALSE on failure).
     *                  Note this value is returned internally to PHP for processing.
     *                  </p>
     * @since 5.4.0
     */
    public function gc(int $max_lifetime): false|int
    {
        return 0;
    }

    /**
     * Initialize session.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $name the session name
     * @return bool <p>
     *                  The return value (usually TRUE on success, FALSE on failure).
     *                  Note this value is returned internally to PHP for processing.
     *                  </p>
     * @since 5.4.0
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * Read session data.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.read.php
     * @return string <p>
     *                    Returns an encoded string of the read data.
     *                    If nothing was read, it must return an empty string.
     *                    Note this value is returned internally to PHP for processing.
     *                    </p>
     * @since 5.4.0
     */
    public function read(string $id): false|string
    {
        return '';
    }

    /**
     * Write session data.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.write.php
     * @return bool <p>
     *                  The return value (usually TRUE on success, FALSE on failure).
     *                  Note this value is returned internally to PHP for processing.
     *                  </p>
     * @since 5.4.0
     */
    public function write(string $id, string $data): bool
    {
        return true;
    }
}
