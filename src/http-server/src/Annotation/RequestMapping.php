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
namespace Hyperf\HttpServer\Annotation;

use Hyperf\Utils\Str;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class RequestMapping extends Mapping
{
    public const GET = 'GET';

    public const POST = 'POST';

    public const PUT = 'PUT';

    public const PATCH = 'PATCH';

    public const DELETE = 'DELETE';

    public const HEADER = 'HEADER';

    public const OPTIONS = 'OPTIONS';

    /**
     * @var array
     */
    public $methods = ['GET', 'POST'];

    public function __construct($value = null)
    {
        parent::__construct($value);
        if (isset($value['methods'])) {
            if (is_string($value['methods'])) {
                // Explode a string to a array
                $this->methods = explode(',', Str::upper(str_replace(' ', '', $value['methods'])));
            } else {
                $methods = [];
                foreach ($value['methods'] as $method) {
                    $methods[] = Str::upper(str_replace(' ', '', $method));
                }
                $this->methods = $methods;
            }
        }
    }
}
