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

namespace Hyperf\Validation\Rules;

use Stringable;

class Dimensions implements Stringable
{
    /**
     * Create a new dimensions rule instance.
     *
     * @param array $constraints the constraints for the dimensions rule
     */
    public function __construct(protected array $constraints = [])
    {
    }

    /**
     * Convert the rule to a validation string.
     */
    public function __toString(): string
    {
        $result = '';

        foreach ($this->constraints as $key => $value) {
            $result .= "{$key}={$value},";
        }

        return 'dimensions:' . substr($result, 0, -1);
    }

    /**
     * Set the "width" constraint.
     */
    public function width(int $value): static
    {
        $this->constraints['width'] = $value;

        return $this;
    }

    /**
     * Set the "height" constraint.
     */
    public function height(int $value): static
    {
        $this->constraints['height'] = $value;

        return $this;
    }

    /**
     * Set the "min width" constraint.
     */
    public function minWidth(int $value): static
    {
        $this->constraints['min_width'] = $value;

        return $this;
    }

    /**
     * Set the "min height" constraint.
     */
    public function minHeight(int $value): static
    {
        $this->constraints['min_height'] = $value;

        return $this;
    }

    /**
     * Set the "max width" constraint.
     */
    public function maxWidth(int $value): static
    {
        $this->constraints['max_width'] = $value;

        return $this;
    }

    /**
     * Set the "max height" constraint.
     */
    public function maxHeight(int $value): static
    {
        $this->constraints['max_height'] = $value;

        return $this;
    }

    /**
     * Set the "ratio" constraint.
     */
    public function ratio(float $value): static
    {
        $this->constraints['ratio'] = $value;

        return $this;
    }
}
