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

namespace Hyperf\Framework\Event;

class BeforeServerStart
{
    /**
     * @var string
     */
    public $serverName;

    public function __construct(string $serverName)
    {
        $this->serverName = $serverName;
    }
}
