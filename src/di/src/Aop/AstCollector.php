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

namespace Hyperf\Di\Aop;

use Hyperf\Di\MetadataCollector;

class AstCollector extends MetadataCollector
{
    /**
     * @var array
     */
    protected static $container = [];
}
