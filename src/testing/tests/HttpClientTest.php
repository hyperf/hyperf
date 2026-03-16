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

namespace tests;

use Hyperf\Testing\HttpClient;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function Hyperf\Coroutine\run;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class HttpClientTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    #[Group('NonCoroutine')]
    public function testJsonRequest()
    {
        run(function () {
            $client = new HttpClient(Mockery::mock(ContainerInterface::class), null, 'http://127.0.0.1:4151');

            $data = $client->get('/stats', [
                'format' => 'json',
            ]);

            $this->assertIsArray($data);
        }, SWOOLE_HOOK_ALL);

        run(function () {
            $client = new HttpClient(Mockery::mock(ContainerInterface::class), null, 'http://127.0.0.1:4151');

            $data = $client->get('/stats', [
                'format' => 'json',
            ]);

            $this->assertIsArray($data);
        }, SWOOLE_HOOK_ALL & ~SWOOLE_HOOK_CURL);
    }
}
