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
    public $server = 'jsonrpc-http';

    /**
     * @var string
     */
    public $protocol = 'jsonrpc-http';

    /**
     * @var string
     */
    public $publishTo = '';
}
