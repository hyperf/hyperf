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

namespace Hyperf\Testing;

class Debug
{
    /**
     * Get object's ref count.
     */
    public static function getRefCount(object $object): string
    {
        ob_start();
        debug_zval_dump($object);
        $data = ob_get_clean();

        preg_match('/refcount\((\w+)\)/U', $data, $matched);
        $result = $matched[1];
        if (is_numeric($result)) {
            return bcsub($result, '1');
        }

        return $matched[1];
    }
}
