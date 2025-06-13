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

namespace HyperfTest\ExceptionHandler;

use Hyperf\Config\Config;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\ExceptionHandler\Annotation\ExceptionHandler;
use Hyperf\ExceptionHandler\Listener\ExceptionHandlerListener;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ExceptionHandlerListenerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        AnnotationCollector::clear();
    }

    public function testConfig()
    {
        $config = new Config([
            'exceptions' => [
                'handler' => [
                    'http' => $http = [
                        'Foo', 'Bar',
                    ],
                    'ws' => $ws = [
                        'Foo', 'Tar', 'Bar',
                    ],
                ],
            ],
        ]);
        $listener = new ExceptionHandlerListener($config);
        $listener->process(new stdClass());
        $this->assertSame($http, $config->get('exceptions.handler', [])['http']);
        $this->assertSame($ws, $config->get('exceptions.handler', [])['ws']);
    }

    public function testAnnotation()
    {
        $config = new Config([
            'exceptions' => [
                'handler' => [
                    'http' => [
                        'Foo', 'Bar',
                    ],
                ],
            ],
        ]);
        AnnotationCollector::collectClass('Bar1', ExceptionHandler::class, new ExceptionHandler('http', 1));
        $listener = new ExceptionHandlerListener($config);
        $listener->process(new stdClass());
        $this->assertSame([
            'http' => [
                'Bar1', 'Foo', 'Bar',
            ],
        ], $config->get('exceptions.handler', []));
    }

    public function testAnnotationWithSamePriotity()
    {
        $config = new Config([
            'exceptions' => [
                'handler' => [
                    'http' => [
                        'Foo', 'Bar',
                    ],
                    'ws' => [
                        'Foo',
                    ],
                ],
            ],
        ]);
        AnnotationCollector::collectClass('Bar1', ExceptionHandler::class, new ExceptionHandler('http', 0));
        AnnotationCollector::collectClass('Bar', ExceptionHandler::class, new ExceptionHandler('ws', 1));
        $listener = new ExceptionHandlerListener($config);
        $listener->process(new stdClass());
        $this->assertEquals(['Foo', 'Bar', 'Bar1'], $config->get('exceptions.handler', [])['http']);
        $this->assertEquals(['Bar', 'Foo'], $config->get('exceptions.handler', [])['ws']);
    }

    public function testTheSameHandler()
    {
        $config = new Config([
            'exceptions' => [
                'handler' => [
                    'http' => [
                        'Foo', 'Bar', 'Bar', 'Tar',
                    ],
                ],
            ],
        ]);
        AnnotationCollector::collectClass('Tar', ExceptionHandler::class, new ExceptionHandler('http', 1));
        $listener = new ExceptionHandlerListener($config);
        $listener->process(new stdClass());
        $this->assertSame([
            'http' => [
                'Tar', 'Foo', 'Bar',
            ],
        ], $config->get('exceptions.handler', []));
    }
}
