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

namespace Hyperf\Testing\Fluent;

use Closure;
use Hyperf\Collection\Arr;
use Hyperf\Contract\Arrayable;
use Hyperf\Macroable\Macroable;
use Hyperf\Tappable\Tappable;
use Hyperf\Testing\AssertableJsonString;
use PHPUnit\Framework\Assert as PHPUnit;

class AssertableJson implements Arrayable
{
    use Concerns\Has;
    use Concerns\Matching;
    use Concerns\Debugging;
    use Concerns\Interaction;
    use Macroable;
    use Tappable;

    /**
     * The properties in the current scope.
     *
     * @var array
     */
    private $props;

    /**
     * The "dot" path to the current scope.
     *
     * @var null|string
     */
    private $path;

    /**
     * Create a new fluent, assertable JSON data instance.
     */
    protected function __construct(array $props, ?string $path = null)
    {
        $this->path = $path;
        $this->props = $props;
    }

    /**
     * Instantiate a new "scope" on the first child element.
     *
     * @return $this
     */
    public function first(Closure $callback): self
    {
        $props = $this->prop();

        $path = $this->dotPath();

        PHPUnit::assertNotEmpty(
            $props,
            $path === ''
            ? 'Cannot scope directly onto the first element of the root level because it is empty.'
            : sprintf('Cannot scope directly onto the first element of property [%s] because it is empty.', $path)
        );

        $key = array_keys($props)[0];

        $this->interactsWith($key);

        return $this->scope($key, $callback);
    }

    /**
     * Instantiate a new "scope" on each child element.
     *
     * @return $this
     */
    public function each(Closure $callback): self
    {
        $props = $this->prop();

        $path = $this->dotPath();

        PHPUnit::assertNotEmpty(
            $props,
            $path === ''
            ? 'Cannot scope directly onto each element of the root level because it is empty.'
            : sprintf('Cannot scope directly onto each element of property [%s] because it is empty.', $path)
        );

        foreach (array_keys($props) as $key) {
            $this->interactsWith($key);

            $this->scope($key, $callback);
        }

        return $this;
    }

    /**
     * Create a new instance from an array.
     *
     * @return static
     */
    public static function fromArray(array $data): self
    {
        return new static($data);
    }

    /**
     * Create a new instance from an AssertableJsonString.
     *
     * @return static
     */
    public static function fromAssertableJsonString(AssertableJsonString $json): self
    {
        return static::fromArray($json->json());
    }

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        return $this->props;
    }

    /**
     * Compose the absolute "dot" path to the given key.
     */
    protected function dotPath(string $key = ''): string
    {
        if (is_null($this->path)) {
            return $key;
        }

        return rtrim(implode('.', [$this->path, $key]), '.');
    }

    /**
     * Retrieve a prop within the current scope using "dot" notation.
     *
     * @return mixed
     */
    protected function prop(?string $key = null)
    {
        return Arr::get($this->props, $key);
    }

    /**
     * Instantiate a new "scope" at the path of the given key.
     *
     * @return $this
     */
    protected function scope(string $key, Closure $callback): self
    {
        $props = $this->prop($key);
        $path = $this->dotPath($key);

        PHPUnit::assertIsArray($props, sprintf('Property [%s] is not scopeable.', $path));

        $scope = new static($props, $path);
        $callback($scope);
        $scope->interacted();

        return $this;
    }
}
