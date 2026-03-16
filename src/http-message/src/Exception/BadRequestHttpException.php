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

namespace Hyperf\HttpMessage\Exception;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class BadRequestHttpException extends HttpException
{
    public function __construct($message = null, $code = 0, ?Throwable $previous = null, protected ?ServerRequestInterface $request = null)
    {
        parent::__construct(400, $message, $code, $previous);
    }

    public function setRequest(?ServerRequestInterface $request): static
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }
}
