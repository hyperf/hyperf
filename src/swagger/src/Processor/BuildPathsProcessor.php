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
namespace Hyperf\Swagger\Processor;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Swagger\Annotation\HyperfServer;
use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use OpenApi\Processors\ProcessorInterface;

class BuildPathsProcessor implements ProcessorInterface
{
    public function __invoke(Analysis $analysis)
    {
        $paths = [];
        // Merge @OA\PathItems with the same path.
        if (! Generator::isDefault($analysis->openapi->paths)) {
            var_dump(123123);
            foreach ($analysis->openapi->paths as $annotation) {
                if (empty($annotation->path)) {
                    $annotation->_context->logger->warning($annotation->identity() . ' is missing required property "path" in ' . $annotation->_context);
                } elseif (isset($paths[$annotation->path])) {
                    $paths[$annotation->path]->mergeProperties($annotation);
                    $analysis->annotations->detach($annotation);
                } else {
                    $paths[$annotation->path] = $annotation;
                }
            }
        }

        /** @var OA\Operation[] $operations */
        $operations = $analysis->unmerged()->getAnnotationsOfType(OA\Operation::class);

        // Merge @OA\Operations into existing @OA\PathItems or create a new one.
        foreach ($operations as $operation) {
            $class = $operation->_context->namespace . '\\' . $operation->_context->class;
            /** @var HyperfServer $serverAnnotation */
            $serverAnnotation = AnnotationCollector::getClassAnnotation($class, HyperfServer::class);
            if (! $serverAnnotation) {
                continue;
            }

            $path = $serverAnnotation->name . '|' . $operation->path;

            if ($path) {
                if (empty($paths[$path])) {
                    $paths[$path] = $pathItem = new OA\PathItem(
                        [
                            'path' => $path,
                            '_context' => new Context(['generated' => true], $operation->_context),
                        ]
                    );
                    $analysis->addAnnotation($pathItem, $pathItem->_context);
                }
                if ($paths[$path]->merge([$operation])) {
                    $operation->_context->logger->warning('Unable to merge ' . $operation->identity() . ' in ' . $operation->_context);
                }
            }
        }
        if ($paths) {
            $analysis->openapi->paths = array_values($paths);
        }
    }
}
