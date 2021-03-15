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
 * Prevent methods from publishing to RPC servers
 * @Annotation
 * @Target({"METHOD"})
 */
class IgnoreRpcMethod extends AbstractAnnotation
{

}
