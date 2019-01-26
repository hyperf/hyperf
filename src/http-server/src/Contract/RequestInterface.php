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

use Psr\Http\Message\ServerRequestInterface;

interface RequestInterface extends ServerRequestInterface
{
    public function input(?string $key = null, $default = null);

    public function inputs(array $keys, $default = null): array;

    public function header(string $key = null, $default = null);

    /**
     * @return []array [found, not-found]
     */
    public function hasInput(array $keys = []): array;
}
