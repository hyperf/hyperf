<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * Date: 2019/9/11
 * Time: 15:53
 * Email: languageusa@163.com
 * Author: Dickens7
 */

namespace Hyperf\Session;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * Class SessionFactory
 * @package Hyperf\Session
 */
class SessionFactory
{
    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * @var Session
     */
    public $session;

    public function __construct(ContainerInterface $container, ConfigInterface $config)
    {
        $this->container = $container;
        $attributes = $config->get('session');
        $session = make(Session::class);
        foreach ($attributes as $key => $value) {
            $methodName = 'set' . strtoupper($key);
            if (method_exists($session, $methodName)) {
                $session->$methodName($value);
            }
        }
        $this->session = $session;
    }

    public function get(string $sessionId = ''): Session
    {
        $session = Context::get(Session::class);
        if (!$session instanceof Session) {
            $request = $this->container->get(RequestInterface::class);
            if (empty($sessionId)) {
                $sessionId = $request->cookie($this->session->getSessionName(), '');
            }
            $request = $this->container->get(RequestInterface::class);

            $session = clone $this->session;

            $session->start($request, $sessionId);
            Context::set(Session::class, $session);
        }
        return $session;
    }
}