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
namespace Hyperf\ViewEngine\Engine;

use Closure;
use Hyperf\ViewEngine\Contract\EngineInterface;
use Hyperf\ViewEngine\Contract\EngineResolverInterface;
use InvalidArgumentException;

class EngineResolver implements EngineResolverInterface
{
    /**
     * The array of engine resolvers.
     *
     * @var array
     */
    protected $resolvers = [];

    /**
     * The resolved engine instances.
     *
     * @var array
     */
    protected $resolved = [];

    /**
     * Register a new engine resolver.
     *
     * The engine string typically corresponds to a file extension.
     */
    public function register(string $engine, Closure $resolver)
    {
        unset($this->resolved[$engine]);

        $this->resolvers[$engine] = $resolver;
    }

    /**
     * Resolve an engine instance by name.
     *
     * @throws InvalidArgumentException
     */
    public function resolve(string $engine): EngineInterface
    {
        if (isset($this->resolved[$engine])) {
            return $this->resolved[$engine];
        }

        if (isset($this->resolvers[$engine])) {
            return $this->resolved[$engine] = call_user_func($this->resolvers[$engine]);
        }

        throw new InvalidArgumentException("Engine [{$engine}] not found.");
    }

    public static function getInstance($resolvers = [])
    {
        $resolver = new EngineResolver();

        foreach ($resolvers as $engine => $engineResolver) {
            $resolver->register($engine, function () use ($engineResolver) {
                return make($engineResolver);
            });
        }

        return $resolver;
    }
}
