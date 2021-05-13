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
namespace Hyperf\HttpServer\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestHandled
{
    /**
     * @var null|ServerRequestInterface
     */
    public $request;

    /**
     * @var null|ResponseInterface
     */
    public $response;

    public function __construct(?ServerRequestInterface $request, ?ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
