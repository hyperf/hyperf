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

use Swoole\Http\Request;
use Swoole\Http\Response;

interface OnHandShakeInterface
{
    /**
     * @param Request $request
     * @param Response $response
     */
    public function onHandShake($request, $response): void;
}
