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
namespace Hyperf\ViewEngine;

use Hyperf\Utils\HtmlString as BaseHtmlString;
use Hyperf\ViewEngine\Contract\Htmlable;

/**
 * @deprecated
 * @package Hyperf\ViewEngine
 */
class HtmlString extends BaseHtmlString implements Htmlable
{
}
