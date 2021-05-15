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
namespace HyperfTest\Crontab;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Crontab\Annotation\Crontab as CrontabAnnotation;
use Hyperf\Crontab\CrontabManager;
use Hyperf\Crontab\Listener\CrontabRegisterListener;
use Hyperf\Crontab\Parser;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Reflection\ClassInvoker;
use HyperfTest\Crontab\Stub\FooCron;
use HyperfTest\Crontab\Stub\FooCron2;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class CrontabRegisterListenerTest extends TestCase
{
    protected $prevContainer;

    protected function setUp(): void
    {
        if (ApplicationContext::hasContainer()) {
            $this->prevContainer = ApplicationContext::getContainer();
        }
    }

    protected function tearDown(): void
    {
        if ($this->prevContainer instanceof ContainerInterface) {
            ApplicationContext::setContainer($this->prevContainer);
        }
    }

    public function testBuildCrontabWithoutConstructorByAnnotationEnableMethod()
    {
        $crontabAnnotation = new Crontab();
        $crontabAnnotation->callback = 'execute';
        $crontabAnnotation->enable = 'isEnable';
        $crontabAnnotation->collectClass(FooCron2::class);

        $annotationCrontabs = AnnotationCollector::getClassesByAnnotation(CrontabAnnotation::class);

        $manager = new CrontabManager(new Parser());
        $class = new ClassInvoker(new CrontabRegisterListener($manager, Mockery::mock(StdoutLoggerInterface::class), Mockery::mock(ConfigInterface::class)));

        $crontab = $class->buildCrontabByAnnotation($annotationCrontabs[FooCron2::class]);

        $this->assertTrue($crontab->isEnable());
    }

    public function testBuildCrontabWithConstructorByAnnotationEnableMethod()
    {
        $crontabAnnotation = new Crontab();
        $crontabAnnotation->callback = 'execute';
        $crontabAnnotation->enable = 'isEnable';
        $crontabAnnotation->collectClass(FooCron::class);

        $annotationCrontabs = AnnotationCollector::getClassesByAnnotation(CrontabAnnotation::class);

        $manager = new CrontabManager(new Parser());
        $class = new ClassInvoker(new CrontabRegisterListener($manager, Mockery::mock(StdoutLoggerInterface::class), Mockery::mock(ConfigInterface::class)));

        $crontab = $class->buildCrontabByAnnotation($annotationCrontabs[FooCron::class]);

        $this->assertFalse($crontab->isEnable());
    }

    public function testBuildCrontabWithStaticMethodByAnnotationEnableMethod()
    {
        $crontabAnnotation = new Crontab();
        $crontabAnnotation->callback = 'execute';
        $crontabAnnotation->enable = 'isEnableCrontab';
        $crontabAnnotation->collectClass(FooCron::class);

        $annotationCrontabs = AnnotationCollector::getClassesByAnnotation(CrontabAnnotation::class);

        $manager = new CrontabManager(new Parser());
        $class = new ClassInvoker(new CrontabRegisterListener($manager, Mockery::mock(StdoutLoggerInterface::class), Mockery::mock(ConfigInterface::class)));
        $crontab = $class->buildCrontabByAnnotation($annotationCrontabs[FooCron::class]);

        $this->assertTrue($crontab->isEnable());
    }
}
