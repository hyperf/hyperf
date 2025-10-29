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

namespace Hyperf\Database\Model\Concerns;

use Closure;

use function Hyperf\Support\value;

trait HidesAttributes
{
    /**
     * The attributes that should be hidden for serialization.
     */
    protected array $hidden = [];

    /**
     * The attributes that should be visible in serialization.
     */
    protected array $visible = [];

    /**
     * Get the hidden attributes for the model.
     */
    public function getHidden(): array
    {
        return $this->hidden;
    }

    /**
     * Set the hidden attributes for the model.
     */
    public function setHidden(array $hidden): static
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Add hidden attributes for the model.
     *
     * @param null|array|string $attributes
     */
    public function addHidden($attributes = null): void
    {
        $this->hidden = array_merge(
            $this->hidden,
            is_array($attributes) ? $attributes : func_get_args()
        );
    }

    /**
     * Get the visible attributes for the model.
     */
    public function getVisible(): array
    {
        return $this->visible;
    }

    /**
     * Set the visible attributes for the model.
     */
    public function setVisible(array $visible): static
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Add visible attributes for the model.
     *
     * @param null|array|string $attributes
     */
    public function addVisible($attributes = null): void
    {
        $this->visible = array_merge(
            $this->visible,
            is_array($attributes) ? $attributes : func_get_args()
        );
    }

    /**
     * Make the given, typically hidden, attributes visible.
     *
     * @param array|string $attributes
     */
    public function makeVisible($attributes): static
    {
        $this->hidden = array_diff($this->hidden, (array) $attributes);

        if (! empty($this->visible)) {
            $this->addVisible($attributes);
        }

        return $this;
    }

    /**
     * Make the given, typically visible, attributes hidden.
     *
     * @param array|string $attributes
     */
    public function makeHidden($attributes): static
    {
        $attributes = (array) $attributes;

        $this->visible = array_diff($this->visible, $attributes);

        $this->hidden = array_unique(array_merge($this->hidden, $attributes));

        return $this;
    }

    /**
     * Make the given, typically visible, attributes hidden if the given truth test passes.
     */
    public function makeHiddenIf(bool|Closure $condition, null|array|string $attributes): static
    {
        return value($condition, $this) ? $this->makeHidden($attributes) : $this;
    }

    /**
     * Make the given, typically hidden, attributes visible if the given truth test passes.
     */
    public function makeVisibleIf(bool|Closure $condition, null|array|string $attributes): static
    {
        return value($condition, $this) ? $this->makeVisible($attributes) : $this;
    }
}
