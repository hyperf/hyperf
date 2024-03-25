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

use RuntimeException;

/**
 * Persists cookies in the client session for FPM.
 */
class SessionCookieJar extends CookieJar
{
    /**
     * Create a new SessionCookieJar object.
     *
     * @param string $sessionKey Session key name to store the cookie
     *                           data in session
     * @param bool $storeSessionCookies Control whether to persist session cookies or not.
     *                                  set to true to store session cookies
     *                                  in the cookie jar
     */
    public function __construct(private string $sessionKey, private bool $storeSessionCookies = false)
    {
        $this->load();
    }

    /**
     * Saves cookies to session when shutting down.
     */
    public function __destruct()
    {
        $this->save();
    }

    /**
     * Save cookies to the client session.
     */
    public function save(): void
    {
        $json = [];
        foreach ($this as $cookie) {
            /** @var SetCookie $cookie */
            if (CookieJar::shouldPersist($cookie, $this->storeSessionCookies)) {
                $json[] = $cookie->toArray();
            }
        }

        $_SESSION[$this->sessionKey] = json_encode($json);
    }

    /**
     * Load the contents of the client session into the data array.
     */
    protected function load(): void
    {
        if (! isset($_SESSION[$this->sessionKey])) {
            return;
        }
        $data = json_decode($_SESSION[$this->sessionKey], true);
        if (is_array($data)) {
            foreach ($data as $cookie) {
                $this->setCookie(new SetCookie($cookie));
            }
        } elseif (strlen($data)) {
            throw new RuntimeException('Invalid cookie data');
        }
    }
}
