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
use Hyperf\Stringable\Str;

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

    public function __construct(?string $path = null, array|string $methods = ['GET', 'POST'], array $options = [])
    {
        $formatted = [];
        if (is_string($methods)) {
            $formatted = explode(',', Str::upper(str_replace(' ', '', $methods)));
        } else {
            foreach ($methods as $method) {
                $formatted[] = Str::upper(str_replace(' ', '', $method));
            }
        }

        parent::__construct($path, $formatted, $options);
    }
}
