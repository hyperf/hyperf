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
namespace Hyperf\Utils;

/**
 * @deprecated since 3.1, please use `\Hyperf\Tappable\HigherOrderTapProxy` instead.
 */
class HigherOrderTapProxy
{
    /**
     * Create a new tap proxy instance.
     * @param mixed $target the target being tapped
     */
    public function __construct(public mixed $target)
    {
    }

    /**
     * Dynamically pass method calls to the target.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $this->target->{$method}(...$parameters);

        return $this->target;
    }
}
