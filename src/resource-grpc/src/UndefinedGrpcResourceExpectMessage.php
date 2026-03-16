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

namespace Hyperf\ResourceGrpc;

use Exception;

class UndefinedGrpcResourceExpectMessage extends Exception
{
    public function __construct(public GrpcResource $resource)
    {
        $message = sprintf('You must override except() and return the message class that for this resource in class [%s].', $resource::class);

        parent::__construct($message, 500);
    }
}
