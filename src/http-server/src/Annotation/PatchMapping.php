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

#[Attribute(Attribute::TARGET_METHOD)]
class PatchMapping extends Mapping
{
    public function __construct(?string $path = null, array $options = [])
    {
        parent::__construct($path, ['PATCH'], $options);
    }
}
