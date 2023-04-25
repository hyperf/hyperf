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
namespace Hyperf\Utils\CodeGen;

use Hyperf\Support\Composer;

/**
 * Read composer.json autoload psr-4 rules to figure out the namespace or path.
 * @deprecated since 3.1, use \Hyperf\CodeParser\Project instead.
 */
class Project extends \Hyperf\CodeParser\Project
{
}
