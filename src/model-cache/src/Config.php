<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\ModelCache;

class Config
{
    /**
     * Model cache key.
     *
     * mc:$prefix:m:$model:$pk:$id
     * You can rewrite it in Redis cluster, for examqple {mc:$prefix:m:$model}:$pk:$id
     * @var string
     */
    protected $cacheKey = 'mc:%s:m:%s:%s:%s';

    /**
     * @var string
     */
    protected $prefix = 'hyperf';

    /**
     * @var int
     */
    protected $ttl = 3600;

    /**
     * @var bool
     */
    protected $loadScript = true;

    public function __construct(array $values, string $name)
    {
        if (isset($values['cache_key'])) {
            $this->cacheKey = $values['cache_key'];
        }
        if (isset($values['prefix'])) {
            $this->prefix = $values['prefix'];
        } else {
            $this->prefix = $name;
        }
        if (isset($values['ttl'])) {
            $this->ttl = $values['ttl'];
        }
        if (isset($values['load_script'])) {
            $this->loadScript = $values['load_script'];
        }
    }

    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    public function setCacheKey(string $cacheKey): Config
    {
        $this->cacheKey = $cacheKey;
        return $this;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): Config
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }

    public function setTtl(int $ttl): Config
    {
        $this->ttl = $ttl;
        return $this;
    }

    public function isLoadScript(): bool
    {
        return $this->loadScript;
    }

    public function setLoadScript(bool $loadScript): Config
    {
        $this->loadScript = $loadScript;
        return $this;
    }
}
