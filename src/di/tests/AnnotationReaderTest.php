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
namespace HyperfTest\Di;

use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;
use Hyperf\Di\Annotation\AnnotationReader;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AnnotationReaderTest extends TestCase
{
    public function testAddGlobalImports()
    {
        AnnotationReader::addGlobalImports('AnnotationStub', 'AnnotationStub');
        $ref = new \ReflectionClass(AnnotationReader::class);
        $properties = $ref->getStaticProperties();
        $this->assertSame([
            'ignoreannotation' => IgnoreAnnotation::class,
            'annotationstub' => 'AnnotationStub',
        ], $properties['globalImports']);
    }
}
