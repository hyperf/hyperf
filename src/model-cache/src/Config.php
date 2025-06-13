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

namespace Hyperf\ModelCache;

use DateInterval;

class Config
{
    /**
     * Model cache key.
     *
     * mc:$prefix:m:$model:$pk:$id
     * You can rewrite it in Redis cluster, for example {mc:$prefix:m:$model}:$pk:$id
     */
    protected string $cacheKey = 'mc:%s:m:%s:%s:%s';

    protected string $prefix = 'hyperf';

    protected string $pool = 'default';

    /**
     * The lifetime of model cache.
     */
    protected DateInterval|int $ttl = 3600;

    /**
     * The lifetime of empty model cache.
     */
    protected int $emptyModelTtl = 60;

    /**
     * Whether to use default value when resolved from cache.
     */
    protected bool $useDefaultValue = false;

    protected bool $loadScript = true;

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
        if (isset($values['pool'])) {
            $this->pool = $values['pool'];
        }
        if (isset($values['ttl'])) {
            $this->ttl = $values['ttl'];
        }
        if (isset($values['load_script'])) {
            $this->loadScript = $values['load_script'];
        }
        if (isset($values['empty_model_ttl'])) {
            $this->emptyModelTtl = $values['empty_model_ttl'];
        }
        if (isset($values['use_default_value'])) {
            $this->useDefaultValue = (bool) $values['use_default_value'];
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

    public function isUseDefaultValue(): bool
    {
        return $this->useDefaultValue;
    }

    public function setUseDefaultValue(bool $useDefaultValue): Config
    {
        $this->useDefaultValue = $useDefaultValue;
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

    public function getPool(): string
    {
        return $this->pool;
    }

    public function setPool(string $pool): Config
    {
        $this->pool = $pool;
        return $this;
    }

    public function getTtl(): DateInterval|int
    {
        return $this->ttl;
    }

    public function setTtl(DateInterval|int $ttl): Config
    {
        $this->ttl = $ttl;
        return $this;
    }

    public function getEmptyModelTtl(): int
    {
        return $this->emptyModelTtl;
    }

    public function setEmptyModelTtl(int $emptyModelTtl): Config
    {
        $this->emptyModelTtl = $emptyModelTtl;
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
