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
namespace HyperfTest\JsonRpcClient\Stub;

use Hyperf\JsonRpcClient\Client;

/**
 * @method string id(string $id)
 * @method void exception()
 */
class IdGenerator extends Client
{
}
