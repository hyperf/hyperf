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
namespace Hyperf\Command\Annotation;

use Hyperf\Di\MetadataCollector;

class CommandCollector extends MetadataCollector
{
    protected static array $container = [];
}
