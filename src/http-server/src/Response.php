<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpServer;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Exception\HttpException;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Jsonable;

class Response implements ResponseInterface
{
    public function json($data)
    {
        if (is_array($data)) {
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        if ($data instanceof Arrayable) {
            return json_encode($data->toArray(), JSON_UNESCAPED_UNICODE);
        }

        if ($data instanceof Jsonable) {
            return (string)$data;
        }

        throw new HttpException('Error encoding response data to JSON.');
    }

    public function raw($data)
    {
        if (is_string($data)) {
            return $data;
        }

        return (string) $data;
    }
}
