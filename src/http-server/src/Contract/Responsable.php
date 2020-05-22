<?php
declare(strict_types=1);

namespace Hyperf\HttpServer\Contract;

use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Jsonable;

interface Responsable
{
    /**
     * @return array|Arrayable|Jsonable|string
     */
    public function toResponse();
}