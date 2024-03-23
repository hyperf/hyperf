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

namespace Hyperf\Contract;

interface OnRequestInterface
{
    /**
     * @param mixed $request swoole request or psr server request
     * @param mixed $response swoole response or swow session
     */
    public function onRequest($request, $response): void;
}
