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

namespace Hyperf\SuperGlobals\Proxy;

use Hyperf\HttpServer\Request as HyperfRequest;
use Hyperf\SuperGlobals\Exception\InvalidOperationException;
use Hyperf\SuperGlobals\Proxy;
use Psr\Http\Message\ServerRequestInterface;

class Request extends Proxy
{
    public function toArray(): array
    {
        if (! $this->hasRequest()) {
            return [];
        }
        $request = $this->getContainer()->get(HyperfRequest::class);
        return $request->all();
    }

    protected function override(ServerRequestInterface $request, array $data): ServerRequestInterface
    {
        throw new InvalidOperationException('Invalid operation for $_REQUEST.');
    }
}
