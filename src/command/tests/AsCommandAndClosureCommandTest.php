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
use Hyperf\Command\ClosureCommand;
use Hyperf\Command\Console;
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
 * @method void registerClosureCommands()
 * @property string $signature
 */
#[CoversNothing]
class AsCommandAndClosureCommandTest extends TestCase
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
    }

    protected function tearDown(): void
    {
        Mockery::close();
        $this->containerSet = [];
    }

    public function testRegisterAsCommand()
    {
        $container = $this->container;
        (fn () => $this->registerAnnotationCommands())->call(
            new RegisterCommandListener($container, $container->get(ConfigInterface::class), $container->get(StdoutLoggerInterface::class))
        );

        $commands = array_values($this->containerSet);
        $this->assertCount(3, $commands);

        $runCommand = $commands[0];
        $runCommandDefinition = $runCommand->getDefinition();
        $this->assertEquals($this->getSignature($runCommand), 'command:as-command:run');
        $this->assertEquals(count($runCommandDefinition->getOptions()), 1);
        $this->assertEquals(count($runCommandDefinition->getArguments()), 0);
        $this->assertNotNull($runCommandDefinition->getOption('disable-event-dispatcher'));

        $runWithDefinedOptionsCommand = $commands[1];
        $runWithDefinedOptionsCommandDefinition = $runWithDefinedOptionsCommand->getDefinition();
        $this->assertEquals($this->getSignature($runWithDefinedOptionsCommand), 'command:as-command:runWithDefinedOptions {--name=}');
        $this->assertEquals(count($runWithDefinedOptionsCommandDefinition->getOptions()), 2);
        $this->assertEquals(count($runWithDefinedOptionsCommandDefinition->getArguments()), 0);
        $this->assertNotNull($runCommandDefinition->getOption('disable-event-dispatcher'));
        $this->assertNotNull($runWithDefinedOptionsCommandDefinition->getOption('name'));

        $runWithoutOptionsCommand = $commands[2];
        $runWithoutOptionsCommandDefinition = $runWithoutOptionsCommand->getDefinition();
        $this->assertEquals($this->getSignature($runWithoutOptionsCommand), 'command:as-command:runWithoutOptions');
        $this->assertEquals(count($runWithoutOptionsCommandDefinition->getOptions()), 4);
        $this->assertEquals(count($runWithoutOptionsCommandDefinition->getArguments()), 0);
        $this->assertNotNull($runCommandDefinition->getOption('disable-event-dispatcher'));
        $this->assertNotNull($runWithoutOptionsCommandDefinition->getOption('name'));
        $this->assertNotNull($runWithoutOptionsCommandDefinition->getOption('age'));
        $this->assertNotNull($runWithoutOptionsCommandDefinition->getOption('testBool'));
    }

    public function testRegisterClosureCommand()
    {
        $runCommand = Console::command('command:closure:run', function () {
            return 'closure';
        });
        $runCommandDefinition = $runCommand->getDefinition();
        $this->assertEquals($this->getSignature($runCommand), 'command:closure:run');
        $this->assertEquals(count($runCommandDefinition->getOptions()), 1);
        $this->assertEquals(count($runCommandDefinition->getArguments()), 0);
        $this->assertNotNull($runCommandDefinition->getOption('disable-event-dispatcher'));

        $runWithDefinedOptionsCommand = Console::command('command:closure:withDefineOptions {--name=}', function (string $name) {
            return 'with define options';
        });
        $runWithDefinedOptionsCommandDefinition = $runWithDefinedOptionsCommand->getDefinition();
        $this->assertEquals($this->getSignature($runWithDefinedOptionsCommand), 'command:closure:withDefineOptions {--name=}');
        $this->assertEquals(count($runWithDefinedOptionsCommandDefinition->getOptions()), 2);
        $this->assertEquals(count($runWithDefinedOptionsCommandDefinition->getArguments()), 0);
        $this->assertNotNull($runCommandDefinition->getOption('disable-event-dispatcher'));
        $this->assertNotNull($runWithDefinedOptionsCommandDefinition->getOption('name'));

        $runWithoutOptionsCommand = Console::command('command:closure:withoutDefineOptions', function (string $name, int $age = 9, bool $testBool = false) {
            return 'with define options';
        });
        $runWithoutOptionsCommandDefinition = $runWithoutOptionsCommand->getDefinition();
        $this->assertEquals($this->getSignature($runWithoutOptionsCommand), 'command:closure:withoutDefineOptions');
        $this->assertEquals(count($runWithoutOptionsCommandDefinition->getOptions()), 4);
        $this->assertEquals(count($runWithoutOptionsCommandDefinition->getArguments()), 0);
        $this->assertNotNull($runCommandDefinition->getOption('disable-event-dispatcher'));
        $this->assertNotNull($runWithoutOptionsCommandDefinition->getOption('name'));
        $this->assertNotNull($runWithoutOptionsCommandDefinition->getOption('age'));
        $this->assertNotNull($runWithoutOptionsCommandDefinition->getOption('testBool'));
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

    protected function getSignature(AsCommand|ClosureCommand $asCommand): string
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

        $container->shouldReceive('get')->with(ClosureCommand::class)->andReturn(ClosureCommand::class);

        // closure command
        $container->shouldReceive('make')->with(ClosureCommand::class, Mockery::any())
            ->andReturnUsing(function ($class, $arguments) {
                return new ClosureCommand($this->container, $arguments['signature'], $arguments['closure']);
            });

        return $container;
    }
}
