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
namespace Hyperf\Command;

/**
 * @deprecated since 3.0.27, remove in 3.1.0, use \Hyperf\Command\Concerns\Confirmable instead.
 */
trait ConfirmableTrait
{
    use Concerns\Confirmable;
}
