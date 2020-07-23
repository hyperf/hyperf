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
namespace Hyperf\Redis\Lua;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Redis\Exception\RedisNotFoundException;
use Psr\Container\ContainerInterface;

abstract class Script implements ScriptInterface
{
    /**
     * PHPRedis client or proxy client.
     * @var mixed|\Redis
     */
    protected $redis;

    /**
     * @var null|string
     */
    protected $sha;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        if ($container->has(\Redis::class)) {
            $this->redis = $container->get(\Redis::class);
        }

        if ($container->has(StdoutLoggerInterface::class)) {
            $this->logger = $container->get(StdoutLoggerInterface::class);
        }
    }

    public function eval(array $arguments = [], $sha = true)
    {
        if ($this->redis === null) {
            throw new RedisNotFoundException('Redis client is not found.');
        }

        if ($sha) {
            $result = $this->redis->evalSha($this->getSha(), $arguments, $this->getKeyNumber($arguments));
            if ($result !== false) {
                return $this->format($result);
            }

            $this->sha = null;
            $this->logger && $this->logger->warning(sprintf('NOSCRIPT No matching script[%s]. Use EVAL instead.', static::class));
        }

        $result = $this->redis->eval($this->getScript(), $arguments, $this->getKeyNumber($arguments));

        return $this->format($result);
    }

    protected function getKeyNumber(array $arguments): int
    {
        return count($arguments);
    }

    protected function getSha(): string
    {
        if (! empty($this->sha)) {
            return $this->sha;
        }

        return $this->sha = $this->redis->script('load', $this->getScript());
    }
}
