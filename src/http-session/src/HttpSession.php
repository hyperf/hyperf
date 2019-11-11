<?php
declare(strict_types=1);

namespace Hyperf\HttpSession;

use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpSession\Exception\SessionIdNotFoundException;
use Hyperf\HttpSession\Handler\RedisHandler;
use Psr\Container\ContainerInterface;

/**
 * Class HttpSession
 * @package Hyperf\HttpSession
 */
class HttpSession {

    /**
     * @var RedisHandler
     */
    public $handler;
    /**
     * @var ContainerInterface
     */
    public $container;
    /**
     * @var RequestInterface
     */
    protected $request;
    protected $sessionId;
    protected $sessionName = 'SESSION_ID';
    protected $maxLifetime = 7200;
    protected $prefix = 'SESSION_ID:';

    public function __construct(ContainerInterface $container, ConfigInterface $config, RequestInterface $request) {
        $this->request = $request;
        $this->container = $container;
        $attributes = $config->get('session');
        foreach ($attributes as $key => $value) {
            $methodName = 'set' . strtoupper($key);
            if (method_exists($this, $methodName)) {
                $this->$methodName($value);
            }
        }
    }

    public function set(string $key, $value): void {
        $sessionData = $this->get();
        $sessionData[$key] = $value;
        $this->handler->set($this->getSessionKey(), $sessionData, $this->maxLifetime);
    }

    public function get(string $key = '') {

        $sessionData = $this->handler->get($this->getSessionKey());
        if ($key === '') {
            return $sessionData;
        }
        if (!isset($sessionData[$key])) {
            return null;
        }
        return $sessionData[$key];
    }

    public function delete(): bool {
        return $this->handler->delete($this->sessionId);
    }

    private function getSessionName(): string {
        return $this->sessionName;
    }

    private function getSessionKey() {

        return $this->prefix . $this->getSessionId();
    }

    private function getSessionId(): string {
        $this->sessionId = $this->request->getCookieParams()[$this->sessionName] ?? '';
        if (empty($this->sessionId)) {
            throw new SessionIdNotFoundException('sessionId not found');
        }
        return $this->sessionId;
    }

    private function setSessionName(string $sessionName): void {
        $this->sessionName = $sessionName;
    }

    private function setPrefix(string $prefix) {
        $this->prefix = $prefix;
    }

    private function setMaxLifetime(int $maxLifetime) {
        $this->maxLifetime = $maxLifetime;
    }

    private function setHandler($handler) {
        $this->handler = new $handler['class'];
        foreach ($handler as $key => $value) {
            $methodName = 'set' . strtoupper($key);
            if (method_exists($this->handler, $methodName)) {
                $this->handler->$methodName($value);
            }
        }
    }
}
