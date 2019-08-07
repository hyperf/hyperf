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

namespace HyperfTest\Config\Stub;

class ProviderConfig extends \Hyperf\Config\ProviderConfig
{
    public static function merge(...$arrays): array
    {
        return parent::merge(...$arrays);
    }
}
