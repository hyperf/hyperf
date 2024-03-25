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

namespace Hyperf\Nats;

/**
 * Class Exception.
 */
class Exception extends \Exception
{
    /**
     * Creates an Exception for a failed connection.
     *
     * @param string $response the failed error response
     */
    public static function forFailedConnection($response)
    {
        return new static(sprintf('Failed to connect: %s', $response));
    }

    /**
     * Creates an Exception for a failed PING response.
     *
     * @param string $response the failed PING response
     */
    public static function forFailedPing($response)
    {
        return new static(sprintf('Failed to ping: %s', $response));
    }

    /**
     * Creates an Exception for an invalid Subscription Identifier (sid).
     *
     * @param string $subscription the Subscription Identifier (sid)
     */
    public static function forSubscriptionNotFound($subscription)
    {
        return new static(sprintf('Subscription not found: %s', $subscription));
    }

    /**
     * Creates an Exception for an invalid Subscription Identifier (sid) callback.
     *
     * @param string $subscription the Subscription Identifier (sid)
     */
    public static function forSubscriptionCallbackInvalid($subscription)
    {
        return new static(sprintf('Subscription callback is invalid: %s', $subscription));
    }

    /**
     * Creates an Exception for the failed creation of a Stream Socket Client.
     *
     * @param string $message the system level error message
     * @param int $code the system level error code
     */
    public static function forStreamSocketClientError($message, $code)
    {
        return new static(sprintf('A Stream Socket Client could not be created: (%d) %s', $code, $message), $code);
    }
}
