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
namespace Hyperf\ModelCache\Redis;

use Hyperf\ModelCache\Config;
use Hyperf\ModelCache\Exception\OperatorNotFoundException;
use Hyperf\Redis\RedisProxy;

use function Hyperf\Support\make;

class LuaManager
{
    /**
     * @var array<string, OperatorInterface>
     */
    protected array $operators = [];

    /**
     * @var array<string, string>
     */
    protected array $luaShas = [];

    protected RedisProxy $redis;

    public function __construct(protected Config $config)
    {
        $this->redis = make(RedisProxy::class, ['pool' => $config->getPool()]);
        $this->operators[HashGetMultiple::class] = new HashGetMultiple();
        $this->operators[HashIncr::class] = new HashIncr();
    }

    public function handle(string $key, array $keys, ?int $num = null)
    {
        if ($this->config->isLoadScript()) {
            $sha = $this->getLuaSha($key);
        }

        $operator = $this->getOperator($key);

        if ($num === null) {
            $num = count($keys);
        }

        if (! empty($sha)) {
            $luaData = $this->redis->evalSha($sha, $keys, $num);
        } else {
            $script = $operator->getScript();
            $luaData = $this->redis->eval($script, $keys, $num);
        }

        return $operator->parseResponse($luaData);
    }

    public function getOperator(string $key): OperatorInterface
    {
        if (! isset($this->operators[$key])) {
            throw new OperatorNotFoundException(sprintf('The operator %s is not found.', $key));
        }

        if (! $this->operators[$key] instanceof OperatorInterface) {
            throw new OperatorNotFoundException(sprintf('The operator %s is not instanceof OperatorInterface.', $key));
        }

        return $this->operators[$key];
    }

    public function getLuaSha(string $key): string
    {
        if (empty($this->luaShas[$key])) {
            $this->luaShas[$key] = $this->redis->script('load', $this->getOperator($key)->getScript());
        }
        return $this->luaShas[$key];
    }
}
