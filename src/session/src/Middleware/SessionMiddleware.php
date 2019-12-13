<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Session\Middleware;

use Carbon\Carbon;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\SessionInterface;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\Session\SessionManager;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionMiddleware implements MiddlewareInterface
{
    /**
     * @var SessionManager
     */
    private $sessionManager;

    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(SessionManager $sessionManager, ConfigInterface $config)
    {
        $this->sessionManager = $sessionManager;
        $this->config = $config;
    }

    /**
     * Process an incoming server request.
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! $this->isSessionAvailable()) {
            return $handler->handle($request);
        }

        $session = $this->sessionManager->start($request);

        $response = $handler->handle($request);

        $this->storeCurrentUrl($request, $session);

        $response = $this->addCookieToResponse($request, $response, $session);

        // @TODO Use defer
        $this->sessionManager->end($session);

        return $response;
    }

    private function isSessionAvailable(): bool
    {
        return $this->config->has('session.handler');
    }

    /**
     * Get the URL (no query string) for the request.
     */
    private function url(RequestInterface $request): string
    {
        return rtrim(preg_replace('/\?.*/', '', $request->getUri()));
    }

    /**
     * Store the current URL for the request if necessary.
     */
    private function storeCurrentUrl(RequestInterface $request, SessionInterface $session)
    {
        if ($request->getMethod() === 'GET') {
            $session->setPreviousUrl($this->fullUrl($request));
        }
    }

    /**
     * Get the session lifetime in seconds.
     */
    private function getCookieExpirationDate(): int
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
    private function addCookieToResponse(
        ServerRequestInterface $request,
        ResponseInterface $response,
        SessionInterface $session
    ): ResponseInterface {
        $uri = $request->getUri();
        $path = '/';
        $domain = $uri->getHost();
        $secure = strtolower($uri->getScheme()) === 'https';
        $httpOnly = true;
        if (! method_exists($response, 'withCookie')) {
            // @TODO Adapte original response object.
            throw new \RuntimeException('Unsupport response object.');
        }
        /* @var \Hyperf\HttpMessage\Server\Response $response */
        return $response->withCookie(new Cookie($session->getName(), $session->getId(), $this->getCookieExpirationDate(), $path, $domain, $secure, $httpOnly));
    }

    /**
     * Get the full URL for the request.
     */
    private function fullUrl(RequestInterface $request): string
    {
        $uri = $request->getUri();
        $query = $uri->getQuery();
        $question = $uri->getHost() . $uri->getPath() == '/' ? '/?' : '?';
        return $query ? $this->url($request) . $question . $query : $this->url($request);
    }
}
