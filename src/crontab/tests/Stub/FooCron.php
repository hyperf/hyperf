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

namespace HyperfTest\Crontab\Stub;

use Hyperf\Contract\ConfigInterface;

class FooCron
{
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config->get('enable', false);
    }

    public function execute()
    {
    }

    public function isEnable()
    {
        return (bool) $this->config;
    }

    public static function isEnableCrontab(): bool
    {
        return true;
    }

    protected function bar()
    {
    }
}
