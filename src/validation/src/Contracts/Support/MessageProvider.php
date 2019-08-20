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

namespace Hyperf\Validation\Contracts\Support;

interface MessageProvider
{
    /**
     * Get the messages for the instance.
     *
     * @return MessageBag
     */
    public function getMessageBag();
}
