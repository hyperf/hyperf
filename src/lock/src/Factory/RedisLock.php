<?php


namespace Hyperf\Lock\Factory;


use Hyperf\Contract\ContainerInterface;
use Hyperf\Lock\Exception\LockException;
use Hyperf\Redis\Redis;
use Hyperf\Utils\Context;

class RedisLock implements LockInterface
{
    /**
     * @var Redis
     */
    protected $redis;

    protected $config = [
        'driver' => 'redis',
        // lock expired time (millisecond)
        'lock_expired' => 10,
        // with retry lock time (millisecond)
        'with_time' => 30,
        'retry' => 1,
    ];

    public function __construct(ContainerInterface $container, array $config = [])
    {
        $this->redis = $container->get(Redis::class);
        $this->config = array_replace_recursive($this->config, $config);
    }

    public function lock(string $id): bool
    {
        return retry(
            $this->config['retry'],
            function () use ($id) {
                $lockKey = $this->getLockKey($id);
                $lockContent = $this->getLockContent();
                $result = $this->redis->set(
                    $lockKey,
                    $lockContent,
                    ['nx', 'ex' => $this->config['lock_expired'] / 1000]
                );
                if ($result === false) {
                    throw new LockException('lock fail');
                }
                return true;
            },
            $this->config['with_time']
        );
    }

    public function unlock(string $id): bool
    {
        $script = <<<LUA
local value = redis.call("get",KEYS[1])

if value 
then
    if value == ARGV[1]
    then
        return redis.call("del",KEYS[1])
    else
        return false
    end
else
    return true
end
LUA;
        $lockKey = $this->getLockKey($id);
        $lockContent = $this->getLockContent();
        $result = $this->redis->eval($script, [$lockKey, $lockContent], 1);
        if (! $result) {
            throw new LockException('unlock fail');
        }
        return true;
    }

    protected function getLockKey(string $id): string
    {
        return sprintf('hyperf:lock:$id:%s', $id);
    }

    protected function getLockContent(): string
    {
        $key = 'hyperf.lock.content';
        if (Context::has($key)) {
            $content = Context::get($key);
        } else {
            $content = $this->genderUUID();
            Context::set($key, $content);
        }
        return $content;
    }

    protected function genderUUID(string $prefix = 'hyperf'): string
    {
        $str = md5(uniqid(mt_rand(), true));
        $uuid = substr($str, 0, 8) . '-';
        $uuid .= substr($str, 8, 4) . '-';
        $uuid .= substr($str, 12, 4) . '-';
        $uuid .= substr($str, 16, 4) . '-';
        $uuid .= substr($str, 20, 12);
        return $prefix . $uuid;
    }
}
