<?php

declare(strict_types=1);

namespace HyperfTest\Swagger;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\AnnotationReader;
use Hyperf\Swagger\Generator;
use HyperfTest\Swagger\Stub\ExampleController;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function json_decode;
use function sys_get_temp_dir;
use function uniqid;

final class GeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $reader = new AnnotationReader();
        $class = new \ReflectionClass(ExampleController::class);
        foreach ($reader->getClassAnnotations($class) as $annotation) {
            AnnotationCollector::collectClass($class->getName(), get_class($annotation), $annotation);
        }
        foreach ($class->getMethods() as $method) {
            foreach ($reader->getMethodAnnotations($method) as $annotation) {
                AnnotationCollector::collectMethod($class->getName(), $method->getName(), get_class($annotation), $annotation);
            }
        }
    }

    public function testConfigProviderRegistersListener(): void
    {
        $provider = new \Hyperf\Swagger\ConfigProvider();
        $config = $provider();

        self::assertContains(\Hyperf\Swagger\Listener\BootSwaggerListener::class, $config['listeners']);
    }

    public function testGeneratesJsonAndYaml(): void
    {
        $directory = sys_get_temp_dir() . '/hyperf-swagger-' . uniqid('', true);
        $config = $this->createMock(ConfigInterface::class);
        $config->method('get')->willReturnCallback(static function (string $key, mixed $default = null) use ($directory): mixed {
            return match ($key) {
                'swagger.scan.paths' => [dirname(__DIR__) . '/tests/Stub'],
                'annotations.scan.paths' => [],
                'swagger.processors' => [],
                'swagger.server' => [
                    'http' => [
                        'servers' => [['url' => 'http://127.0.0.1:9501']],
                        'info' => ['title' => 'Swagger compatibility test', 'version' => '1.0.0'],
                    ],
                ],
                'swagger.json_dir' => $directory . '/json',
                'swagger.yaml_dir' => $directory . '/yaml',
                default => $default,
            };
        });

        (new Generator($config))->generate();

        $json = $directory . '/json/http.json';
        $yaml = $directory . '/yaml/http.yaml';
        self::assertFileExists($json);
        self::assertFileExists($yaml);
        $document = json_decode(file_get_contents($json), true, flags: JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('/hyperf/example/index', $document['paths']);
        self::assertStringContainsString('openapi:', file_get_contents($yaml));
        self::assertStringContainsString('/hyperf/example/json:', file_get_contents($yaml));
    }
}
