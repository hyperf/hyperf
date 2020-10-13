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
namespace Hyperf\Utils;

class Resource
{
    /**
     * @return false|resource
     */
    public static function from(string $body, ?string $filename = null)
    {
        if (is_null($filename)) {
            $filename = 'php://temp';
        }
        $resource = fopen($filename, 'r+');
        if ($body !== '') {
            fwrite($resource, $body);
            fseek($resource, 0);
        }

        return $resource;
    }
}
