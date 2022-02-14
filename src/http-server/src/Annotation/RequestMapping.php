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

use Attribute;
use Hyperf\Utils\Str;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
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

    public function __construct(...$value)
    {
        parent::__construct(...$value);
        $formattedValue = $this->formatParams($value);
        if (isset($formattedValue['methods'])) {
            if (is_string($formattedValue['methods'])) {
                // Explode a string to a array
                $this->methods = explode(',', Str::upper(str_replace(' ', '', $formattedValue['methods'])));
            } else {
                $methods = [];
                foreach ($formattedValue['methods'] as $method) {
                    $methods[] = Str::upper(str_replace(' ', '', $method));
                }
                $this->methods = $methods;
            }
        }
    }
}
