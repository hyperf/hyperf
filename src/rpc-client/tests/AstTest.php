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

namespace HyperfTest\RpcClient;

use Hyperf\RpcClient\Proxy\Ast;
use HyperfTest\RpcClient\Stub\FooInterface;
use HyperfTest\RpcClient\Stub\ParInterface;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class AstTest extends TestCase
{
    protected $license = '<?php

declare (strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */';

    public function testAstGenerate()
    {
        $ast = new Ast();

        $code = $ast->proxy(ParInterface::class, 'ParClient');

        $this->assertSame($this->license . '
namespace HyperfTest\RpcClient\Stub;

class ParClient extends \Hyperf\RpcClient\Proxy\AbstractProxyService implements ParInterface
{
    public function getName() : string
    {
        return $this->client->__call(__FUNCTION__, func_get_args());
    }
}', $code);

        $code = $ast->proxy(FooInterface::class, 'FooClient');

        $this->assertSame($this->license . '
namespace HyperfTest\RpcClient\Stub;

class FooClient extends \Hyperf\RpcClient\Proxy\AbstractProxyService implements FooInterface
{
    public function getId() : int
    {
        return $this->client->__call(__FUNCTION__, func_get_args());
    }
    public function getName() : string
    {
        return $this->client->__call(__FUNCTION__, func_get_args());
    }
}', $code);
    }
}
