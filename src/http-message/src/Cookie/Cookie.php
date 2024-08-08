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

namespace Hyperf\HttpMessage\Cookie;

use DateTimeInterface;
use InvalidArgumentException;
use Stringable;

class Cookie implements Stringable
{
    public const SAMESITE_LAX = 'lax';

    public const SAMESITE_STRICT = 'strict';

    public const SAMESITE_NONE = 'none';

    protected int $expire;

    protected string $path;

    private ?string $sameSite = null;

    /**
     * @param string $name The name of the cookie
     * @param string $value The value of the cookie
     * @param DateTimeInterface|int|string $expire The time the cookie expires
     * @param string $path The path on the server in which the cookie will be available on
     * @param string $domain The domain that the cookie is available to
     * @param bool $secure Whether the cookie should only be transmitted over a secure HTTPS connection from the client
     * @param bool $httpOnly Whether the cookie will be made accessible only through the HTTP protocol
     * @param bool $raw Whether the cookie value should be sent with no url encoding
     * @param null|string $sameSite Whether the cookie will be available for cross-site requests
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        protected string $name,
        protected string $value = '',
        $expire = 0,
        string $path = '/',
        protected string $domain = '',
        protected bool $secure = false,
        protected bool $httpOnly = true,
        protected bool $raw = false,
        ?string $sameSite = null,
        protected bool $partitioned = false
    ) {
        // from PHP source code
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }

        if (empty($name)) {
            throw new InvalidArgumentException('The cookie name cannot be empty.');
        }

        // convert expiration time to a Unix timestamp
        if ($expire instanceof DateTimeInterface) {
            $expire = $expire->format('U');
        } elseif (! is_numeric($expire)) {
            $expire = strtotime($expire);

            if ($expire === false) {
                throw new InvalidArgumentException('The cookie expiration time is not valid.');
            }
        }

        $this->expire = 0 < $expire ? (int) $expire : 0;
        $this->path = empty($path) ? '/' : $path;

        if ($sameSite !== null) {
            $sameSite = strtolower($sameSite);
        }

        if (! in_array($sameSite, [self::SAMESITE_LAX, self::SAMESITE_STRICT, self::SAMESITE_NONE, null], true)) {
            throw new InvalidArgumentException('The "sameSite" parameter value is not valid.');
        }

        $this->sameSite = $sameSite;
    }

    /**
     * Returns the cookie as a string.
     *
     * @return string The cookie
     */
    public function __toString()
    {
        $str = ($this->isRaw() ? $this->getName() : urlencode($this->getName())) . '=';

        if ($this->getValue() === '') {
            $str .= 'deleted; expires=' . gmdate('D, d-M-Y H:i:s T', time() - 31536001) . '; max-age=-31536001';
        } else {
            $str .= $this->isRaw() ? $this->getValue() : rawurlencode($this->getValue());

            if ($this->getExpiresTime() !== 0) {
                $str .= '; expires=' . gmdate(
                    'D, d-M-Y H:i:s T',
                    $this->getExpiresTime()
                ) . '; max-age=' . $this->getMaxAge();
            }
        }

        if ($this->getPath()) {
            $str .= '; path=' . $this->getPath();
        }

        if ($this->getDomain()) {
            $str .= '; domain=' . $this->getDomain();
        }

        if ($this->isSecure() === true) {
            $str .= '; secure';
        }

        if ($this->isHttpOnly() === true) {
            $str .= '; httponly';
        }

        if ($this->getSameSite() !== null) {
            $str .= '; samesite=' . $this->getSameSite();
        }

        if ($this->isPartitioned()) {
            $str .= '; partitioned';
        }

        return $str;
    }

    /**
     * Creates cookie from raw header string.
     */
    public static function fromString(string $cookie, bool $decode = false): self
    {
        $data = [
            'expires' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => false,
            'raw' => ! $decode,
            'samesite' => null,
            'partitioned' => false,
        ];
        foreach (explode(';', $cookie) as $part) {
            if (! str_contains($part, '=')) {
                $key = trim($part);
                $value = true;
            } else {
                [$key, $value] = explode('=', trim($part), 2);
                $key = trim($key);
                $value = trim($value);
            }
            if (! isset($data['name'])) {
                $data['name'] = $decode ? urldecode($key) : $key;
                $data['value'] = $value === true ? null : ($decode ? urldecode($value) : $value);
                continue;
            }
            switch ($key = strtolower($key)) {
                case 'name':
                case 'value':
                    break;
                case 'max-age':
                    $data['expires'] = time() + (int) $value;
                    break;
                default:
                    $data[$key] = $value;
                    break;
            }
        }

        return new Cookie(
            $data['name'],
            $data['value'],
            $data['expires'],
            $data['path'],
            $data['domain'],
            $data['secure'],
            $data['httponly'],
            $data['raw'],
            $data['samesite'],
            $data['partitioned']
        );
    }

    /**
     * Gets the name of the cookie.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the value of the cookie.
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Gets the domain that the cookie is available to.
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Gets the time the cookie expires.
     */
    public function getExpiresTime(): int
    {
        return $this->expire;
    }

    /**
     * Gets the max-age attribute.
     */
    public function getMaxAge(): int
    {
        return $this->expire !== 0 ? $this->expire - time() : 0;
    }

    /**
     * Gets the path on the server in which the cookie will be available on.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Checks whether the cookie should only be transmitted over a secure HTTPS connection from the client.
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * Checks whether the cookie will be made accessible only through the HTTP protocol.
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * Whether this cookie is about to be cleared.
     */
    public function isCleared(): bool
    {
        return $this->expire < time();
    }

    /**
     * Checks if the cookie value should be sent with no url encoding.
     */
    public function isRaw(): bool
    {
        return $this->raw;
    }

    /**
     * Gets the SameSite attribute.
     */
    public function getSameSite(): ?string
    {
        return $this->sameSite;
    }

    /**
     * Checks whether the cookie should be tied to the top-level site in cross-site context.
     */
    public function isPartitioned(): bool
    {
        return $this->partitioned;
    }
}
