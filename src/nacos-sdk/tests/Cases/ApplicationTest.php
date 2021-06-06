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
namespace HyperfTest\NacosSdk\Cases;

use Hyperf\NacosSdk\Application;
use Hyperf\NacosSdk\Config;
use Hyperf\NacosSdk\Provider\AuthProvider;
use Hyperf\NacosSdk\Provider\ConfigProvider as ConfigCenter;
use Hyperf\NacosSdk\Provider\InstanceProvider;
use Hyperf\NacosSdk\Provider\OperatorProvider;
use Hyperf\NacosSdk\Provider\ServiceProvider;
use HyperfTest\NacosSdk\AbstractTestCase;

/**
 * @internal
 * @coversNothing
 */
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
