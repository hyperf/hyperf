<?php
declare(strict_types=1);

namespace Hyperf\HttpSession\Handler;

use Hyperf\Redis\RedisFactory;
use Hyperf\Utils\ApplicationContext;

/**
 * Class RedisHandler
 * @package Hyperf\HttpSession\Handler
 */
class RedisHandler implements HandlerInterface {

    /**
     * @var RedisFactory
     */
    protected $redis;
    public $poolName = 'default';

    public function __construct() {

        $container = ApplicationContext::getContainer();
        $this->redis = $container->get(RedisFactory::class)->get($this->poolName);
    }

    public function get(string $sessionId): array {
        return $this->decode((string)$this->redis->get($sessionId));
    }

    public function delete(string $sessionId): bool {
        return (bool)$this->redis->delete($sessionId);
    }

    public function set(string $sessionId, array $sessionData, int $maxLifetime): bool {
        return $this->redis->setex($sessionId, $maxLifetime, serialize($sessionData));
    }

    public function decode(string $data): array {
        if (!$data) {
            return [];
        }
        return unserialize($data);
    }

    public function setPoolName(string $poolName) {
        $this->poolName = $poolName;
    }
}
