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

namespace Hyperf\Tappable;

/**
 * @template TValue
 */
class HigherOrderTapProxy
{
    /**
     * The target being tapped.
     *
     * @var TValue
     */
    public $target;

    /**
     * Create a new tap proxy instance.
     *
     * @param TValue of object $target
     */
    public function __construct($target)
    {
        $this->target = $target;
    }

    /**
     * Dynamically pass method calls to the target.
     *
     * @return TValue
     */
    public function __call(string $name, array $arguments): mixed
    {
        $this->target->{$name}(...$arguments);

        return $this->target;
    }
}
