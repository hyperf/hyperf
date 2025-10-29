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

namespace Hyperf\Constants\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
class Message
{
    public function __construct(public string $value, public string $key = 'message')
    {
    }

    public function getLowerCaseKey(): string
    {
        return strtolower(str_replace('_', '', $this->key));
    }
}
