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
namespace Hyperf\Contract;

interface SessionInterface
{
    /**
     * Starts the session storage.
     *
     * @throws \RuntimeException if session fails to start
     * @return bool True if session started
     */
    public function start(): bool;

    /**
     * Returns the session ID.
     *
     * @return string The session ID
     */
    public function getId(): string;

    /**
     * Sets the session ID.
     */
    public function setId(string $id);

    /**
     * Returns the session name.
     */
    public function getName(): string;

    /**
     * Sets the session name.
     */
    public function setName(string $name);

    /**
     * Invalidates the current session.
     *
     * Clears all session attributes and flashes and regenerates the
     * session and deletes the old session from persistence.
     *
     * @param int $lifetime Sets the cookie lifetime for the session cookie. A null value
     *                      will leave the system settings unchanged, 0 sets the cookie
     *                      to expire with browser session. Time is in seconds, and is
     *                      not a Unix timestamp.
     *
     * @return bool True if session invalidated, false if error
     */
    public function invalidate(?int $lifetime = null): bool;

    /**
     * Migrates the current session to a new session id while maintaining all
     * session attributes.
     *
     * @param bool $destroy Whether to delete the old session or leave it to garbage collection
     * @param int $lifetime Sets the cookie lifetime for the session cookie. A null value
     *                      will leave the system settings unchanged, 0 sets the cookie
     *                      to expire with browser session. Time is in seconds, and is
     *                      not a Unix timestamp.
     *
     * @return bool True if session migrated, false if error
     */
    public function migrate(bool $destroy = false, ?int $lifetime = null): bool;

    /**
     * Force the session to be saved and closed.
     *
     * This method is generally not required for real sessions as
     * the session will be automatically saved at the end of
     * code execution.
     */
    public function save(): void;

    /**
     * Checks if an attribute is defined.
     *
     * @param string $name The attribute name
     *
     * @return bool true if the attribute is defined, false otherwise
     */
    public function has(string $name): bool;

    /**
     * Returns an attribute.
     *
     * @param string $name The attribute name
     * @param mixed $default The default value if not found
     */
    public function get(string $name, $default = null);

    /**
     * Sets an attribute.
     * @param mixed $value
     */
    public function set(string $name, $value): void;

    /**
     * Put a key / value pair or array of key / value pairs in the session.
     *
     * @param array|string $key
     * @param null|mixed $value
     */
    public function put($key, $value = null): void;

    /**
     * Returns attributes.
     */
    public function all(): array;

    /**
     * Sets attributes.
     */
    public function replace(array $attributes): void;

    /**
     * Removes an attribute, returning its value.
     *
     * @return mixed The removed value or null when it does not exist
     */
    public function remove(string $name);

    /**
     * Remove one or many items from the session.
     *
     * @param array|string $keys
     */
    public function forget($keys): void;

    /**
     * Clears all attributes.
     */
    public function clear(): void;

    /**
     * Checks if the session was started.
     */
    public function isStarted(): bool;

    /**
     * Get the previous URL from the session.
     */
    public function previousUrl(): ?string;

    /**
     * Set the "previous" URL in the session.
     */
    public function setPreviousUrl(string $url): void;
}
