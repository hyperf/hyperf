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

use Hyperf\Codec\Json;
use RuntimeException;

/**
 * Persists non-session cookies using a JSON formatted file.
 */
class FileCookieJar extends CookieJar
{
    /**
     * Create a new FileCookieJar object.
     *
     * @param string $filename File to store the cookie data
     * @param bool $storeSessionCookies Control whether to persist session cookies or not.
     *                                  Set to true to store session cookies in the cookie jar.
     *
     * @throws RuntimeException if the file cannot be found or created
     */
    public function __construct(private string $filename, private bool $storeSessionCookies = false)
    {
        if (file_exists($filename)) {
            $this->load($filename);
        }
    }

    /**
     * Saves the file when shutting down.
     */
    public function __destruct()
    {
        $this->save($this->filename);
    }

    /**
     * Saves the cookies to a file.
     *
     * @param string $filename File to save
     * @throws RuntimeException if the file cannot be found or created
     */
    public function save(string $filename): void
    {
        $json = [];
        foreach ($this as $cookie) {
            /** @var SetCookie $cookie */
            if (CookieJar::shouldPersist($cookie, $this->storeSessionCookies)) {
                $json[] = $cookie->toArray();
            }
        }

        $jsonStr = Json::encode($json);
        if (file_put_contents($filename, $jsonStr) === false) {
            throw new RuntimeException("Unable to save file {$filename}");
        }
    }

    /**
     * Load cookies from a JSON formatted file.
     *
     * Old cookies are kept unless overwritten by newly loaded ones.
     *
     * @param string $filename cookie file to load
     * @throws RuntimeException if the file cannot be loaded
     */
    public function load(string $filename): void
    {
        $json = file_get_contents($filename);
        if ($json === false) {
            throw new RuntimeException("Unable to load file {$filename}");
        }
        if ($json === '') {
            return;
        }

        $data = Json::decode($json);
        if (is_array($data)) {
            foreach ($data as $cookie) {
                $this->setCookie(new SetCookie($cookie));
            }
        } elseif (strlen($data)) {
            throw new RuntimeException("Invalid cookie file: {$filename}");
        }
    }
}
