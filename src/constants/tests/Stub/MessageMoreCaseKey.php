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

namespace HyperfTest\Constants\Stub;

use Hyperf\Constants\Annotation\Constants;
use Hyperf\Constants\Annotation\Message;
use Hyperf\Constants\EnumConstantsTrait;

/**
 * @method string getSnakeKey()
 * @method string getSnakeKey1()
 * @method string getCamelCase()
 * @method string getBigCamel()
 * @method string getLowercaseValue()
 */
#[Constants]
enum MessageMoreCaseKey
{
    use EnumConstantsTrait;

    #[Message('snake key value', 'snake_key')]
    #[Message('snake key1 value', 'snake_key_1')]
    #[Message('camel case value', 'camelCase')]
    #[Message('big camel case value', 'BigCamel')]
    #[Message('value of lowercase key', 'lowercasevalue')]
    case FOO;
}
