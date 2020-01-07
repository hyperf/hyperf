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
use Hyperf\Utils\Str;
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
        $headers = [];
        foreach ($this->getRequest()->getHeaders() as $key => $value) {
            $headers['HTTP_' . str_replace('-', '_', Str::upper($key))] = $value;
        }

        return array_merge($this->default, $this->getRequest()->getServerParams(), $headers);
    }

    protected function override(ServerRequestInterface $request, array $data): ServerRequestInterface
    {
        throw new InvalidOperationException('Invalid operation for $_SERVER.');
    }
}
