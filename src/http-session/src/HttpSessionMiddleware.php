<?php
declare(strict_types=1);

namespace Hyperf\HttpSession;

use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpServer\Contract\ResponseInterface as HyperfResponse;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Hyperf\Utils\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class HttpSessionMiddleware
 * @package Hyperf\HttpSession
 */
class HttpSessionMiddleware implements MiddlewareInterface {

    protected $sessionName = 'SESSION_ID';
    protected $cookieDomain = '';
    protected $cookiePath = '/';
    protected $cookieExpires = 0;
    protected $cookieSecure = false;
    protected $cookieHttpOnly = true;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {

        $sessionId = $request->getCookieParams()[$this->sessionName] ?? '';
        if (empty($sessionId)) {
            $sessionId = Str::random(32);
            var_dump(array_merge([$this->sessionName => $sessionId], $request->getCookieParams()));
            $request = $request->withCookieParams(array_merge([$this->sessionName => $sessionId], $request->getCookieParams()));
            $container = ApplicationContext::getContainer();
            $response = $container->get(HyperfResponse::class);
            $response = $response->withCookie(new Cookie(
                $this->sessionName,
                $sessionId,
                $this->cookieExpires,
                $this->cookiePath,
                $this->cookieDomain,
                $this->cookieSecure,
                $this->cookieHttpOnly
            ));
            Context::set(ResponseInterface::class, $response);
            Context::set(ServerRequestInterface::class, $request);
        }
        return $handler->handle($request);
    }
}
