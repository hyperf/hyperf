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

class Dimensions
{
    /**
     * The constraints for the dimensions rule.
     *
     * @var array
     */
    protected $constraints = [];

    /**
     * Create a new dimensions rule instance.
     */
    public function __construct(array $constraints = [])
    {
        $this->constraints = $constraints;
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
     *
     * @return $this
     */
    public function width(int $value)
    {
        $this->constraints['width'] = $value;

        return $this;
    }

    /**
     * Set the "height" constraint.
     *
     * @return $this
     */
    public function height(int $value)
    {
        $this->constraints['height'] = $value;

        return $this;
    }

    /**
     * Set the "min width" constraint.
     *
     * @return $this
     */
    public function minWidth(int $value)
    {
        $this->constraints['min_width'] = $value;

        return $this;
    }

    /**
     * Set the "min height" constraint.
     *
     * @return $this
     */
    public function minHeight(int $value)
    {
        $this->constraints['min_height'] = $value;

        return $this;
    }

    /**
     * Set the "max width" constraint.
     *
     * @return $this
     */
    public function maxWidth(int $value)
    {
        $this->constraints['max_width'] = $value;

        return $this;
    }

    /**
     * Set the "max height" constraint.
     *
     * @return $this
     */
    public function maxHeight(int $value)
    {
        $this->constraints['max_height'] = $value;

        return $this;
    }

    /**
     * Set the "ratio" constraint.
     *
     * @return $this
     */
    public function ratio(float $value)
    {
        $this->constraints['ratio'] = $value;

        return $this;
    }
}
