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
namespace Hyperf\Resource;

use Hyperf\Resource\Grpc\GrpcResource;

class UndefinedGrpcResourceExpectMessage extends \Exception
{
    public $resource;

    public function __construct(GrpcResource $resource)
    {
        $this->resource = $resource;

        $message = sprintf('You must override except() and return the message class that for this resource in class [%s].', get_class($resource));

        parent::__construct($message, 500);
    }
}
