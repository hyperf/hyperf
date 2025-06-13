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

namespace HyperfTest\Config\Stub;

class ProviderConfig extends \Hyperf\Config\ProviderConfig
{
    public static function loadProviders(array $providers): array
    {
        return parent::loadProviders($providers);
    }

    public static function merge(...$arrays): array
    {
        return parent::merge(...$arrays);
    }
}
