<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Crontab;

class Crontab
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $rule;

    /**
     * @var string|null
     */
    public $command;

    /**
     * @var string|null
     */
    public $memo;
}
