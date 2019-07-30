<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\GraphQL;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ApcuCache;

class ReaderFactory
{
    public function __invoke()
    {
        AnnotationRegistry::registerLoader('class_exists');
        $doctrineAnnotationReader = new AnnotationReader();

        if (function_exists('apcu_fetch')) {
            $doctrineAnnotationReader = new CachedReader($doctrineAnnotationReader, new ApcuCache(), true);
        }

        return $doctrineAnnotationReader;
    }
}
