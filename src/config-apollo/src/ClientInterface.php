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

namespace Hyperf\ConfigApollo;

use Psr\Http\Message\ResponseInterface;

interface ClientInterface extends \Hyperf\ConfigCenter\Contract\ClientInterface
{
    public function getOption(): Option;

    public function parallelPull(array $namespaces): array;

    public function longPulling(array $notifications): ?ResponseInterface;
}
