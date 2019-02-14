<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpServer\Contract;

use Hyperf\Utils\Contracts\Jsonable;
use Hyperf\Utils\Contracts\Arrayable;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ResponseInterface
{
    /**
     * Format data to JSON and return data with Content-Type:application/json header.
     *
     * @param array|Arrayable|Jsonable $data
     */
    public function json($data): PsrResponseInterface;

    /**
     * Format data to a string and return data with Content-Type:text/plain header.
     * @param mixed $data
     */
    public function raw($data): PsrResponseInterface;
}
