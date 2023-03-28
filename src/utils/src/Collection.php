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

class_alias(\Hyperf\Collection\Collection::class, Collection::class);

if (! class_exists(Collection::class)) {
    /**
     * @deprecated since 3.1, use \Hyperf\Collection\Collection instead.
     */
    class Collection extends \Hyperf\Collection\Collection
    {
    }
}
