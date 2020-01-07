<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpAuth;

use Hyperf\HttpAuth\Contract\Guard;
use Hyperf\HttpAuth\Contract\HttpAuthContract;
use Hyperf\HttpAuth\Contract\UserProvider;
use Hyperf\HttpAuth\Exception\InvalidArgumentException;
use Hyperf\Contract\ConfigInterface;

class HttpAuthManage implements HttpAuthContract
{
    use ContextHelpers;

    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function __call($method, $parameters)
    {
        return $this->guard()->{$method}(...$parameters);
    }

    /**
     * @param null|string $name
     */
    public function guard($name = null): Guard
    {
        $name = $name ?: $this->getDefaultDriver();

        $guard = $this->getContext('guards::' . $name);

        return $guard ?: $this->setContext('guards::' . $name, $this->resolve($name));
    }

    /**
     * @param string $name
     */
    public function shouldUse($name)
    {
        $name = $name ?: $this->getDefaultDriver();

        $this->setDefaultDriver($name);

        $this->resolveUsersUsing(function ($name = null) {
            return $this->guard($name)->user();
        });
    }

    /**
     * Get the user resolver callback.
     *
     * @return \Closure
     */
    public function userResolver()
    {
        return $this->getContext('userResolver');
    }

    /**
     * Set the callback to be used to resolve users.
     *
     * @return $this
     */
    public function resolveUsersUsing(\Closure $userResolver)
    {
        $this->setContext('userResolver', $userResolver);

        return $this;
    }

    public function setDefaultDriver($name)
    {
        $this->setContext('defaults.guard', $name);
    }

    public function getDefaultDriver()
    {
        return $this->getContext('defaults.guard') ?: $this->config->get('http-auth.defaults.guard');
    }

    /**
     * Create the user provider implementation for the driver.
     *
     * @param null|string $provider
     * @throws \InvalidArgumentException
     * @return null|UserProvider
     */
    public function createUserProvider($provider = null)
    {
        if (is_null($config = $this->getProviderConfiguration($provider))) {
            return null;
        }

        $driver = ($config['driver'] ?? null);

        if ($class = Config::getAnnotation($driver, UserProvider::class)) {
            // error_log("Use User Provider: [{$class}]");
            return make($class, [$config]);
        }
        throw new InvalidArgumentException(
            "Authentication user provider [{$driver}] is not defined."
        );
    }

    /**
     * Get the default user provider name.
     *
     * @return string
     */
    public function getDefaultUserProvider()
    {
        return $this->config->get('http-auth.defaults.provider');
    }

    protected function resolve($name)
    {
        $config = $this->config->get("http-auth.guards.{$name}");

        if (is_null($config)) {
            throw new InvalidArgumentException("Auth guard [{$name}] is not defined.");
        }

        if ($class = Config::getAnnotation($config['driver'] ?? '', Guard::class)) {
            // error_log("Use Guard: [{$class}]");
            return make($class, [$config, $this->createUserProvider($config['provider'] ?? null)]);
        }
        throw new InvalidArgumentException(
            "Auth driver [{$config['driver']}] for guard [{$name}] is not defined."
        );
    }

    /**
     * Get the user provider configuration.
     *
     * @param null|string $provider
     * @return null|array
     */
    protected function getProviderConfiguration($provider)
    {
        if ($provider = $provider ?: $this->getDefaultUserProvider()) {
            return $this->config->get('http-auth.providers.' . $provider);
        }
    }
}
