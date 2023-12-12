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
namespace Hyperf\Swagger\Request;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Swagger\Annotation\JsonContent;
use Hyperf\Swagger\Annotation\MediaType;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\QueryParameter;
use Hyperf\Swagger\Annotation\RequestBody;
use Hyperf\Swagger\Util;

class RuleCollector
{
    protected static array $rules = [];

    public static function get(string $class, string $method): array
    {
        if (! empty(static::$rules[$class][$method])) {
            return static::$rules[$class][$method];
        }
        $rules = [];
        $methodAnnotations = AnnotationCollector::getClassMethodAnnotation($class, $method);

        self::collectQueryParameterRules($methodAnnotations, $rules);
        self::collectRequestBodyRules($methodAnnotations, $rules);
        self::collectMediaTypeRules($methodAnnotations, $rules);

        return static::$rules[$class][$method] = $rules;
    }

    protected static function collectRequestBodyRules($methodAnnotations, &$rules): void
    {
        /** @var RequestBody $body */
        $body = Util::findAnnotations($methodAnnotations, RequestBody::class)[0] ?? null;
        if (! $body) {
            return;
        }

        if (! $body->_content instanceof JsonContent) {
            return;
        }

        if (! is_array($body->_content->properties)) {
            return;
        }

        foreach ($body->_content->properties as $property) {
            if ($property instanceof Property) {
                if ($property->rules) {
                    $rules[$property->property] = $property->rules;
                }
            }
        }
    }

    private static function collectQueryParameterRules($methodAnnotations, &$rules): void
    {
        /** @var QueryParameter[] $queryParameters */
        $queryParameters = Util::findAnnotations($methodAnnotations, QueryParameter::class);

        foreach ($queryParameters as $parameter) {
            if ($parameter->rules) {
                $rules[$parameter->name] = $parameter->rules;
            }
        }
    }

    private static function collectMediaTypeRules($methodAnnotations, &$rules): void
    {
        /** @var RequestBody $body */
        $body = Util::findAnnotations($methodAnnotations, RequestBody::class)[0] ?? null;
        if (! $body || ! is_array($body->content)) {
            return;
        }
        foreach ($body->content as $content) {
            if (! $content instanceof MediaType) {
                continue;
            }
            if (! $content->schema || ! is_array($content->schema->properties)) {
                continue;
            }
            foreach ($content->schema->properties as $property) {
                if ($property instanceof Property) {
                    if ($property->rules) {
                        $rules[$property->property] = $property->rules;
                    }
                }
            }
        }
    }
}
