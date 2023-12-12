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
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\QueryParameter;
use Hyperf\Swagger\Annotation\RequestBody;
use Hyperf\Swagger\Util;

class AttributeCollector
{
    protected static $attributes = [];

    public static function get(string $class, string $method): array
    {
        if (! empty(static::$attributes[$class][$method])) {
            return static::$attributes[$class][$method];
        }
        $methodAnnotations = AnnotationCollector::getClassMethodAnnotation($class, $method);
        /** @var QueryParameter[] $queryParameters */
        $queryParameters = Util::findAnnotations($methodAnnotations, QueryParameter::class);

        $attributes = [];
        foreach ($queryParameters as $parameter) {
            if ($parameter->attribute) {
                $attributes[$parameter->name] = $parameter->attribute;
            }
        }

        /** @var RequestBody $body */
        $body = Util::findAnnotations($methodAnnotations, RequestBody::class)[0] ?? null;
        if ($body) {
            if ($body->_content instanceof JsonContent) {
                if (is_array($body->_content->properties)) {
                    foreach ($body->_content->properties as $property) {
                        if ($property instanceof Property) {
                            if ($property->attribute) {
                                $attributes[$property->property] = $property->attribute;
                            }
                        }
                    }
                }
            }
        }

        return static::$attributes[$class][$method] = $attributes;
    }
}
