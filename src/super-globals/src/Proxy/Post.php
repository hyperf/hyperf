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

use Hyperf\SuperGlobals\Proxy;
use Psr\Http\Message\ServerRequestInterface;

class Post extends Proxy
{
    public function toArray(): array
    {
        if (! $this->hasRequest()) {
            return [];
        }
        return (array) $this->getRequest()->getParsedBody();
    }

    protected function override(ServerRequestInterface $request, array $data): ServerRequestInterface
    {
        return $request->withParsedBody($data);
    }
}
