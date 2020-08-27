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
namespace Hyperf\Session\Listener;

use Carbon\Carbon;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\SessionInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpServer\Event\OnRequestEnd;
use Hyperf\Session\SessionManager;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @Listener
 */
class SessionEnd implements ListenerInterface
{
    /**
     * @var SessionManager
     */
    protected $sessionManager;

    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(SessionManager $sessionManager, ConfigInterface $config)
    {
        $this->sessionManager = $sessionManager;
        $this->config = $config;
    }

    public function listen(): array
    {
        return [
            OnRequestEnd::class,
        ];
    }

    public function process(object $event)
    {
        if ($this->isSessionAvailable()) {
            /* @var OnRequestEnd $event */
            $this->storeCurrentUrl($event->request, $session = $this->sessionManager->getSession());
            $this->sessionManager->end($session);
            $this->addCookieToResponse($event->request, $event->response, $session);
        }
    }

    protected function isSessionAvailable(): bool
    {
        return $this->config->has('session.handler');
    }

    /**
     * Get the URL (no query string) for the request.
     */
    protected function url(RequestInterface $request): string
    {
        return rtrim(preg_replace('/\?.*/', '', (string) $request->getUri()));
    }

    /**
     * Store the current URL for the request if necessary.
     */
    protected function storeCurrentUrl(RequestInterface $request, SessionInterface $session)
    {
        if ($request->getMethod() === 'GET') {
            $session->setPreviousUrl($this->fullUrl($request));
        }
    }

    /**
     * Get the session lifetime in seconds.
     */
    protected function getCookieExpirationDate(): int
    {
        if ($this->config->get('session.options.expire_on_close')) {
            $expirationDate = 0;
        } else {
            $expirationDate = Carbon::now()->addMinutes(5 * 60)->getTimestamp();
        }
        return $expirationDate;
    }

    /**
     * Add the session cookie to the response·.
     */
    protected function addCookieToResponse(
        ServerRequestInterface $request,
        ResponseInterface $response,
        SessionInterface $session
    ): ResponseInterface {
        $uri = $request->getUri();
        $path = '/';
        $secure = strtolower($uri->getScheme()) === 'https';
        $httpOnly = true;

        $domain = $this->config->get('session.options.domain') ?? $uri->getHost();

        $cookie = new Cookie($session->getName(), $session->getId(), $this->getCookieExpirationDate(), $path, $domain, $secure, $httpOnly);
        if (! method_exists($response, 'withCookie')) {
            return $response->withHeader('Set-Cookie', (string) $cookie);
        }
        /* @var \Hyperf\HttpMessage\Server\Response $response */
        return $response->withCookie($cookie);
    }

    /**
     * Get the full URL for the request.
     */
    protected function fullUrl(RequestInterface $request): string
    {
        $uri = $request->getUri();
        $query = $uri->getQuery();
        $question = $uri->getHost() . $uri->getPath() == '/' ? '/?' : '?';
        return $query ? $this->url($request) . $question . $query : $this->url($request);
    }
}
