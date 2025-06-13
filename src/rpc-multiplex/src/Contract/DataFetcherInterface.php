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

namespace Hyperf\RpcMultiplex\Contract;

use Hyperf\RpcClient\Exception\RequestException;

interface DataFetcherInterface
{
    /**
     * @throws RequestException
     */
    public function fetch(array $data): mixed;
}
