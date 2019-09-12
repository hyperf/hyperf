<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * Date: 2019/9/10
 * Time: 16:43
 * Email: languageusa@163.com
 * Author: Dickens7
 */

namespace Hyperf\Session;

use Hyperf\Utils\Str;
use Hyperf\Utils\Context;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\Session\Handler\HandlerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * Class Session
 * @package Hyperf\Session
 */
class Session
{
    /**
     * @var HandlerInterface
     */
    public $handler;

    protected $sessionId;
    protected $sessionName = 'SESSION_ID';
    protected $cookieDomain = '';
    protected $cookiePath = '/';
    protected $cookieExpires = 0;
    protected $cookieSecure = false;
    protected $cookieHttpOnly = true;

    protected $maxLifetime = 7200;
    protected $prefix = 'SESSION:';
    public $sessionData = [];

    protected $sessionPath = '';

    protected $isStart;

    public function start(RequestInterface $request, string $sessionId = ''): bool
    {
        if (!empty($sessionId)) {
            $this->sessionId = $sessionId;
        }
        $this->isStart = $this->handler->open($this->sessionPath, $this->sessionName);
        $sessionId = $request->getCookieParams()[$this->sessionName] ?? '';

        if (empty($sessionId) && empty($this->sessionId)) {
            $this->sessionId = Str::random(32);
        }

        if (empty($this->sessionId)) {
            $this->sessionId = $sessionId;
        }

        if ($sessionId !== $this->sessionId) {
            $request->withCookieParams(array_merge(
                    [
                        $this->sessionName => $this->sessionId
                    ],
                    $request->getCookieParams())
            );
            $response = Context::get(PsrResponseInterface::class);
            $response = $response->withCookie(new Cookie($this->sessionName,
                $this->sessionId,
                $this->cookieExpires,
                $this->cookiePath,
                $this->cookieDomain,
                $this->cookieSecure,
                $this->cookieHttpOnly));
            Context::set(PsrResponseInterface::class, $response);
        }
        $this->sessionData = $this->handler->read($this->getSessionKey());
        return $this->isStart;
    }

    public function set(string $key, $value): void
    {
        $this->sessionData[$key] = $value;
        $this->handler->write($this->getSessionKey(), $this->sessionData, $this->maxLifetime);
    }

    public function get(string $key = '')
    {
        if ($key === '') {
            return $this->sessionData;
        }
        if (!isset($this->sessionData[$key])) {
            return null;
        }
        return $this->sessionData[$key];
    }

    protected function getSessionKey()
    {
        return $this->prefix . $this->sessionId;
    }

    public function destroy(): bool
    {
        $this->sessionData = [];
        return $this->handler->destroy($this->sessionId);
    }

    public function getSessionName(): string
    {
        return $this->sessionName;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function setDomain(string $domain)
    {
        $this->cookieDomain = $domain;
    }

    public function setCookieSecure(bool $cookieSecure)
    {
        $this->cookieSecure = $cookieSecure;
    }

    public function setCookieExpires(int $expires)
    {
        $this->cookieExpires = $expires;
    }

    public function setCookiePath(string $path): void
    {
        $this->sessionPath = $path;
    }

    public function setSessionName(string $sessionName): void
    {
        $this->sessionName = $sessionName;
    }

    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public function setMaxLifetime(int $maxLifetime)
    {
        $this->maxLifetime = $maxLifetime;
    }

    public function setHandler($handler)
    {
        $this->handler = new $handler['class'];
        foreach ($handler as $key => $value) {
            $methodName = 'set' . strtoupper($key);
            if (method_exists($this->handler, $methodName)) {
                $this->handler->$methodName($value);
            }
        }
    }
}