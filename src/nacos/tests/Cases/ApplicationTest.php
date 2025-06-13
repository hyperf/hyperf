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

namespace HyperfTest\Nacos\Cases;

use Hyperf\Nacos\Application;
use Hyperf\Nacos\Config;
use Hyperf\Nacos\Provider\AuthProvider;
use Hyperf\Nacos\Provider\ConfigProvider as ConfigCenter;
use Hyperf\Nacos\Provider\InstanceProvider;
use Hyperf\Nacos\Provider\OperatorProvider;
use Hyperf\Nacos\Provider\ServiceProvider;
use HyperfTest\Nacos\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ApplicationTest extends AbstractTestCase
{
    public function testApplication()
    {
        $application = new Application(new Config());

        $this->assertInstanceOf(AuthProvider::class, $application->auth);
        $this->assertInstanceOf(ConfigCenter::class, $application->config);
        $this->assertInstanceOf(InstanceProvider::class, $application->instance);
        $this->assertInstanceOf(OperatorProvider::class, $application->operator);
        $this->assertInstanceOf(ServiceProvider::class, $application->service);
    }
}
