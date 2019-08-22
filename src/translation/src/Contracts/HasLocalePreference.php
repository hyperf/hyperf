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

namespace Hyperf\Translation\Contracts;

interface HasLocalePreference
{
    /**
     * Get the preferred locale of the entity.
     *
     * @return null|string
     */
    public function preferredLocale();
}
