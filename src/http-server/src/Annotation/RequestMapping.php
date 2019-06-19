<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpServer\Annotation;

use Hyperf\Utils\Str;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class RequestMapping extends Mapping
{
    /**
     * @var array
     */
    public $methods = ['GET', 'POST'];

    public function __construct($value = null)
    {
        parent::__construct($value);
        if (isset($value['methods'])) {
            // Explode a string to a array
            $this->methods = explode(',', Str::upper(str_replace(' ', '', $value['methods'])));
        }
    }
}
