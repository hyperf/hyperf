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

    public array $methods = ['GET', 'POST'];

    public function __construct(...$value)
    {
        $formattedValue = $this->formatParams($value);
        if (isset($formattedValue['methods'])) {
            if (is_string($formattedValue['methods'])) {
                // Explode a string to an array
                $formattedValue['methods'] = explode(',', Str::upper(str_replace(' ', '', $formattedValue['methods'])));
            } else {
                $methods = [];
                foreach ($formattedValue['methods'] as $method) {
                    $methods[] = Str::upper(str_replace(' ', '', $method));
                }
                $formattedValue['methods'] = $methods;
            }

            $value = [$formattedValue];
        }

        parent::__construct(...$value);
    }
}
