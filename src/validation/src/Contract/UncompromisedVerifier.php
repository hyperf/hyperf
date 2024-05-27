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

namespace Hyperf\Validation\Contract;

interface UncompromisedVerifier
{
    /**
     * Verify that the given data has not been compromised in data leaks.
     */
    public function verify(array $data): bool;
}
