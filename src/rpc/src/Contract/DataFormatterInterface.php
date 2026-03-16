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

namespace Hyperf\Rpc\Contract;

use Hyperf\Rpc\ErrorResponse;
use Hyperf\Rpc\Request;
use Hyperf\Rpc\Response;

interface DataFormatterInterface
{
    public function formatRequest(Request $request): array;

    public function formatResponse(Response $response): array;

    public function formatErrorResponse(ErrorResponse $response): array;
}
