<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

use Hyperf\Utils\ApplicationContext;

if (! function_exists('make')) {
    function make(string $name, array $parameters = [])
    {
        return ApplicationContext::getContainer()->make($name, $parameters);
    }
}
