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

namespace Hyperf\Testing\Fluent\Concerns;

use Hyperf\Stringable\Str;
use PHPUnit\Framework\Assert as PHPUnit;

trait Interaction
{
    /**
     * The list of interacted properties.
     *
     * @var array
     */
    protected $interacted = [];

    /**
     * Asserts that all properties have been interacted with.
     */
    public function interacted(): void
    {
        PHPUnit::assertSame(
            [],
            array_diff(array_keys($this->prop()), $this->interacted),
            $this->path
                ? sprintf('Unexpected properties were found in scope [%s].', $this->path)
                : 'Unexpected properties were found on the root level.'
        );
    }

    /**
     * Disables the interaction check.
     *
     * @return $this
     */
    public function etc(): self
    {
        $this->interacted = array_keys($this->prop());

        return $this;
    }

    /**
     * Marks the property as interacted.
     */
    protected function interactsWith(string $key): void
    {
        $prop = Str::before($key, '.');

        if (! in_array($prop, $this->interacted, true)) {
            $this->interacted[] = $prop;
        }
    }

    /**
     * Retrieve a prop within the current scope using "dot" notation.
     *
     * @return mixed
     */
    abstract protected function prop(?string $key = null);
}
