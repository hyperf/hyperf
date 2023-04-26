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
namespace Hyperf\Utils\Exception;

class_alias(\Hyperf\Support\Exception\IPReadFailedException::class, IPReadFailedException::class);

if (! class_exists(IPReadFailedException::class)) {
    /**
     * @deprecated since 3.1, use \Hyperf\Support\Exception\IPReadFailedException instead.
     */
    class IPReadFailedException extends \Hyperf\Support\Exception\IPReadFailedException
    {
    }
}
