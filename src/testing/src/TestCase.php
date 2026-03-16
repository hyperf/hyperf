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

namespace Hyperf\Testing;

use Mockery as m;
use Throwable;

use function Hyperf\Support\class_basename;
use function Hyperf\Support\class_uses_recursive;

/**
 * @internal
 * @coversNothing
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    use Concerns\InteractsWithContainer;
    use Concerns\InteractsWithModelFactory;
    use Concerns\MakesHttpRequests;
    use Concerns\RunTestsInCoroutine;
    use Concerns\InteractsWithDatabase;

    /**
     * The callbacks that should be run after the application is created.
     */
    protected array $afterApplicationCreatedCallbacks = [];

    /**
     * The callbacks that should be run before the application is destroyed.
     */
    protected array $beforeApplicationDestroyedCallbacks = [];

    /**
     * The exception thrown while running an application destruction callback.
     */
    protected ?Throwable $callbackException = null;

    protected function setUp(): void
    {
        $this->refreshContainer();

        $this->setUpTraits();

        foreach ($this->afterApplicationCreatedCallbacks as $callback) {
            $callback();
        }
    }

    protected function tearDown(): void
    {
        $this->flushContainer();

        $this->callBeforeApplicationDestroyedCallbacks();

        try {
            m::close();
        } catch (Throwable $e) {
        }

        if ($this->callbackException) {
            throw $this->callbackException;
        }
    }

    /**
     * Boot the testing helper traits.
     *
     * @return array
     */
    protected function setUpTraits()
    {
        $uses = array_flip(class_uses_recursive(static::class));

        foreach ($uses as $trait) {
            if (method_exists($this, $method = 'setUp' . class_basename($trait))) {
                $this->{$method}();
            }

            if (method_exists($this, $method = 'tearDown' . class_basename($trait))) {
                $this->beforeApplicationDestroyed(fn () => $this->{$method}());
            }
        }

        return $uses;
    }

    /**
     * Register a callback to be run before the application is destroyed.
     */
    protected function beforeApplicationDestroyed(callable $callback)
    {
        $this->beforeApplicationDestroyedCallbacks[] = $callback;
    }

    /**
     * Execute the application's pre-destruction callbacks.
     */
    protected function callBeforeApplicationDestroyedCallbacks()
    {
        foreach ($this->beforeApplicationDestroyedCallbacks as $callback) {
            try {
                $callback();
            } catch (Throwable $e) {
                if (! $this->callbackException) {
                    $this->callbackException = $e;
                }
            }
        }
    }
}
