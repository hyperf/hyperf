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

namespace Hyperf\RpcMultiplex;

class Constant
{
    public const PROTOCOL_DEFAULT = 'multiplex.default';

    public const PROTOCOL_PHP_SERIALIZE = 'multiplex.php_serialize';

    public const REQUEST_ID = 'request_id';

    public const ID = 'id';

    public const PATH = 'path';

    public const DATA = 'data';

    public const RESULT = 'result';

    public const CONTEXT = 'context';

    public const EXTRA = 'extra';

    public const ERROR = 'error';

    public const CODE = 'code';

    public const MESSAGE = 'message';

    public const HOST = 'host';

    public const PORT = 'port';

    public const CHANNEL_ID = 'multiplex.channel_id';

    public const DEFAULT_SETTINGS = [
        'open_length_check' => true,
        'package_length_type' => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 4,
    ];
}
