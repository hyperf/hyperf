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

namespace HyperfTest\Command;

use Hyperf\Command\AsCommand;
use Hyperf\Command\Listener\RegisterCommandListener;
use Hyperf\Command\ParameterParser;
use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AnnotationReader;
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\ClosureDefinitionCollector;
use Hyperf\Di\ClosureDefinitionCollectorInterface;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\Di\ReflectionManager;
use Hyperf\Di\ScanHandler\NullScanHandler;
use Hyperf\Serializer\SerializerFactory;
use Hyperf\Serializer\SymfonyNormalizer;
use HyperfTest\Command\Command\Annotation\TestAsCommand;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 * @method void registerAnnotationCommands()
 * @property string $signature
 */
#[CoversNothing]
class AsCommandTest extends TestCase
{
    /**
     * @var AsCommand[]
     */
    protected array $containerSet = [];

    protected ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        if (! empty($this->containerSet)) {
            return;
        }

        $scanner = new Scanner(new ScanConfig(false, '/'), new NullScanHandler());
        $reader = new AnnotationReader();
        $scanner->collect($reader, ReflectionManager::reflectClass(TestAsCommand::class));

        $this->container = $container = $this->getContainer();

        (fn () => $this->registerAnnotationCommands())->call(
            new RegisterCommandListener($container, $container->get(ConfigInterface::class), $container->get(StdoutLoggerInterface::class))
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        $this->containerSet = [];
    }

    public function testRegister()
    {
        $commands = array_values($this->containerSet);

        $this->assertCount(3, $commands);
        $this->assertEquals($this->getSignature($commands[0]), 'command:testAsCommand:run');
        $this->assertEquals($this->getSignature($commands[1]), 'command:testAsCommand:runWithDefinedOptions {--name=}');
        $this->assertEquals($this->getSignature($commands[2]), 'command:testAsCommand:runWithoutOptions'); // TODO 自动补全
    }

    public function testParameterParser()
    {
        $container = $this->container;
        $parameterParser = $container->get(ParameterParser::class);

        $class = TestAsCommand::class;
        $method = 'runWithoutOptions';
        $arguments = [
            'name' => 'Hyperf',
            'test-bool' => '123', // snake case
        ];

        $result = $parameterParser->parseMethodParameters($class, $method, $arguments);
        $this->assertEquals([
            'Hyperf',
            9,
            true,
        ], $result);
    }

    protected function getSignature(AsCommand $asCommand): string
    {
        return (fn () => $this->signature)->call($asCommand);
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('has')->with(ConfigInterface::class)->andReturnTrue();
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturnUsing(function () {
            return new Config([
            ]);
        });

        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturnUsing(function () {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('debug')->andReturn(null);
            $logger->shouldReceive('log')->andReturn(null);
            return $logger;
        });

        $container->shouldReceive('get')->with(NormalizerInterface::class)->andReturn(new SymfonyNormalizer((new SerializerFactory())->__invoke()));
        $container->shouldReceive('has')->with(NormalizerInterface::class)->andReturn(true);
        $container->shouldReceive('get')->with(MethodDefinitionCollectorInterface::class)->andReturn(new MethodDefinitionCollector());
        $container->shouldReceive('has')->with(MethodDefinitionCollectorInterface::class)->andReturn(true);
        $container->shouldReceive('has')->with(ClosureDefinitionCollectorInterface::class)->andReturn(true);
        $container->shouldReceive('get')->with(ClosureDefinitionCollectorInterface::class)->andReturn(new ClosureDefinitionCollector());

        $container->shouldReceive('get')->with(ParameterParser::class)->andReturn(new ParameterParser($container));
        $container->shouldReceive('get')->with(TestAsCommand::class)->andReturn(new TestAsCommand());

        $container->shouldReceive('set')->withAnyArgs()->andReturnUsing(function ($key, $value) {
            $this->containerSet[$key] = $value;
        });

        return $container;
    }
}
