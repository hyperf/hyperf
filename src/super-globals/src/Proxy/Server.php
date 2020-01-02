<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\SuperGlobals\Proxy;

use Hyperf\SuperGlobals\Exception\InvalidOperationException;
use Hyperf\SuperGlobals\Proxy;
use Psr\Http\Message\ServerRequestInterface;

class Server extends Proxy
{
    /**
     * @var array
     */
    protected $default;

    public function __construct(array $default)
    {
        $this->default = $default;
    }

    public function toArray(): array
    {
        return array_merge($this->default, $this->getRequest()->getServerParams());
    }

    protected function override(ServerRequestInterface $request, array $data): ServerRequestInterface
    {
        throw new InvalidOperationException('Invalid operation for $_SERVER.');
    }
}
