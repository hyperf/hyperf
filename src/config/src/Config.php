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
namespace Hyperf\Config;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Arr;

class Config implements ConfigInterface
{
    /**
     * @var array
     */
    private $configs = [];

    public function __construct(array $configs)
    {
        $this->configs = $configs;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $key identifier of the entry to look for
     * @param mixed $default default value of the entry when does not found
     * @return mixed entry
     */
    public function get(string $key, $default = null)
    {
        return data_get($this->configs, $key, $default);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $key identifier of the entry to look for
     * @return bool
     */
    public function has(string $key)
    {
        return Arr::has($this->configs, $key);
    }

    /**
     * Set a value to the container by its identifier.
     *
     * @param string $key identifier of the entry to set
     * @param mixed $value the value that save to container
     */
    public function set(string $key, $value)
    {
        data_set($this->configs, $key, $value);
    }
}
