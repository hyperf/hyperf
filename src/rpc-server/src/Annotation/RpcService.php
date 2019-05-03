<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\RpcServer\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class RpcService extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $server = 'rpc';

    /**
     * @var string
     */
    public $protocol = 'jsonrpc-20';

    /**
     * @var string
     */
    public $publishTo = 'consul';
}
