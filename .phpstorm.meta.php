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
namespace PHPSTORM_META;

use function di;
use function make;
use function optional;
use function tap;

// Reflect
override(\Psr\Container\ContainerInterface::get(0), map(['' => '@']));
override(\Hyperf\Context\Context::get(0), map(['' => '@']));
override(make(0), map(['' => '@']));
override(\Hyperf\Support\make(0), map(['' => '@']));
override(di(0), map(['' => '@']));
override(optional(0), type(0));
override(\Hyperf\Support\optional(0), type(0));
override(tap(0), type(0));
override(\Hyperf\Tappable\tap(0), type(0));
