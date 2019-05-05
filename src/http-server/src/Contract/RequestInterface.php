<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpServer\Contract;

use Psr\Http\Message\ServerRequestInterface;

interface RequestInterface extends ServerRequestInterface
{
    /**
     * Retrieve the data from query parameters, if $key is null, will return all query parameters.
     * @param mixed $default
     */
    public function query(?string $key = null, $default = null);

    /**
     * Retrieve the data from parsed body, if $key is null, will return all parsed body.
     * @param mixed $default
     */
    public function post(?string $key = null, $default = null);

    /**
     * Retrieve the input data from request, include query parameters, parsed body and json body.
     * @param mixed $default
     */
    public function input(string $key, $default = null);

    /**
     * Retrieve the input data from request via multi keys, include query parameters, parsed body and json body.
     * @param mixed $default
     */
    public function inputs(array $keys, $default = null): array;

    /**
     * Determine if the $keys is exist in parameters.
     * @return []array [found, not-found]
     */
    public function hasInput(array $keys): array;

    /**
     * Retrieve the data from request headers.
     * @param mixed $default
     */
    public function header(string $key, $default = null);
}
