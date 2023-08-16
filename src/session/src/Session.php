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
namespace Hyperf\Session;

use Hyperf\Collection\Arr;
use Hyperf\Contract\SessionInterface;
use Hyperf\Stringable\Str;
use SessionHandlerInterface;

use function Hyperf\Collection\data_get;
use function Hyperf\Collection\data_set;

/**
 * This is a data class, please create a new instance for each request.
 */
class Session implements SessionInterface
{
    use FlashTrait;

    protected string $id;

    protected array $attributes = [];

    /**
     * Session store started status.
     */
    protected bool $started = false;

    public function __construct(protected string $name, protected SessionHandlerInterface $handler, $id = null)
    {
        if (! is_string($id) || ! $this->isValidId($id)) {
            $id = $this->generateSessionId();
        }
        $this->setId($id);
        $this->setName($name);
    }

    /**
     * Determine if this is a valid session ID.
     */
    public function isValidId(string $id): bool
    {
        return ctype_alnum($id) && strlen($id) === 40;
    }

    /**
     * Starts the session storage.
     */
    public function start(): bool
    {
        $this->loadSession();

        return $this->started = true;
    }

    /**
     * Returns the session ID.
     *
     * @return string The session ID
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Sets the session ID.
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * Returns the session name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the session name.
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Invalidates the current session.
     * Clears all session attributes and flashes and regenerates the
     * session and deletes the old session from persistence.
     *
     * @param int $lifetime Sets the cookie lifetime for the session cookie. A null value
     *                      will leave the system settings unchanged, 0 sets the cookie
     *                      to expire with browser session. Time is in seconds, and is
     *                      not a Unix timestamp.
     * @return bool True if session invalidated, false if error
     */
    public function invalidate(?int $lifetime = null): bool
    {
        $this->clear();

        return $this->migrate(true, $lifetime);
    }

    /**
     * Migrates the current session to a new session id while maintaining all
     * session attributes.
     *
     * @param bool $destroy Whether to delete the old session or leave it to garbage collection
     * @param int $lifetime Sets the cookie lifetime for the session cookie. A null value
     *                      will leave the system settings unchanged, 0 sets the cookie
     *                      to expire with browser session. Time is in seconds, and is
     *                      not a Unix timestamp.
     * @return bool True if session migrated, false if error
     */
    public function migrate(bool $destroy = false, ?int $lifetime = null): bool
    {
        if ($destroy) {
            $this->handler->destroy($this->getId());
        }

        $this->setId($this->generateSessionId());

        return true;
    }

    /**
     * Force the session to be saved and closed.
     * This method is generally not required for real sessions as
     * the session will be automatically saved at the end of
     * code execution.
     */
    public function save(): void
    {
        $this->ageFlashData();

        $this->handler->write($this->getId(), $this->prepareForStorage(serialize($this->attributes)));

        $this->started = false;
    }

    /**
     * Checks if an attribute is defined.
     *
     * @param string $name The attribute name
     * @return bool true if the attribute is defined, false otherwise
     */
    public function has(string $name): bool
    {
        return Arr::exists($this->attributes, $name);
    }

    /**
     * Returns an attribute.
     *
     * @param string $name The attribute name
     * @param mixed $default The default value if not found
     */
    public function get(string $name, $default = null)
    {
        return data_get($this->attributes, $name, $default);
    }

    /**
     * Sets an attribute.
     *
     * @param mixed $value
     */
    public function set(string $name, $value): void
    {
        data_set($this->attributes, $name, $value);
    }

    /**
     * Put a key / value pair or array of key / value pairs in the session.
     *
     * @param array|string $key
     * @param null|mixed $value
     */
    public function put($key, $value = null): void
    {
        if (! is_array($key)) {
            $key = [$key => $value];
        }

        foreach ($key as $arrayKey => $arrayValue) {
            Arr::set($this->attributes, $arrayKey, $arrayValue);
        }
    }

    /**
     * Returns attributes.
     */
    public function all(): array
    {
        return $this->attributes;
    }

    /**
     * Sets attributes.
     */
    public function replace(array $attributes): void
    {
        foreach ($attributes as $name => $value) {
            data_set($this->attributes, $name, $value);
        }
    }

    /**
     * Removes an attribute, returning its value.
     *
     * @return mixed The removed value or null when it does not exist
     */
    public function remove(string $name)
    {
        return Arr::pull($this->attributes, $name);
    }

    /**
     * Remove one or many items from the session.
     *
     * @param array|string $keys
     */
    public function forget($keys): void
    {
        Arr::forget($this->attributes, $keys);
    }

    /**
     * Clears all attributes.
     */
    public function clear(): void
    {
        $this->attributes = [];
    }

    /**
     * Checks if the session was started.
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * Get the CSRF token value.
     */
    public function token(): string
    {
        return (string) $this->get('_token');
    }

    /**
     * Regenerate the CSRF token value.
     */
    public function regenerateToken(): string
    {
        $this->put('_token', $token = Str::random(40));
        return $token;
    }

    /**
     * Get the previous URL from the session.
     */
    public function previousUrl(): ?string
    {
        $previousUrl = $this->get('_previous.url');
        if (! is_string($previousUrl)) {
            $previousUrl = null;
        }
        return $previousUrl;
    }

    /**
     * Set the "previous" URL in the session.
     */
    public function setPreviousUrl(string $url): void
    {
        $this->set('_previous.url', $url);
    }

    /**
     * Push a value onto a session array.
     *
     * @param mixed $value
     */
    public function push(string $key, $value): void
    {
        $array = $this->get($key, []);

        $array[] = $value;

        $this->put($key, $array);
    }

    /**
     * Generate a new random session ID.
     */
    protected function generateSessionId(): string
    {
        return Str::random(40);
    }

    /**
     * Load the session data from the handler.
     */
    protected function loadSession(): void
    {
        $this->attributes = array_merge($this->attributes, $this->readFromHandler());
    }

    /**
     * Read the session data from the handler.
     */
    protected function readFromHandler(): array
    {
        if ($data = $this->handler->read($this->getId())) {
            $data = @unserialize($this->prepareForUnserialize($data));

            if ($data !== false && is_array($data)) {
                return $data;
            }
        }

        return [];
    }

    /**
     * Prepare the raw string data from the session for serialization.
     */
    protected function prepareForUnserialize(string $data): string
    {
        return $data;
    }

    /**
     * Prepare the serialized session data for storage.
     */
    protected function prepareForStorage(string $data): string
    {
        return $data;
    }
}
