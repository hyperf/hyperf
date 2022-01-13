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
namespace Hyperf\Utils\CodeGen;

use PhpDocReader\PhpDocReader;

class PhpDocReaderManager
{
    /**
     * @var null|PhpDocReader
     */
    protected static $instance;

    public static function getInstance(): PhpDocReader
    {
        if (static::$instance) {
            return static::$instance;
        }
        return static::$instance = new PhpDocReader();
    }
}
