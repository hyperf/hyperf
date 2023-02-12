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

class RuleCollector
{
    protected static $rules = [];

    public static function get(string $class, string $method): array
    {
        if (! empty(static::$rules[$class][$method])) {
            return static::$rules[$class][$method];
        }
        $methodAnnotations = AnnotationCollector::getClassMethodAnnotation($class, $method);
        /** @var QueryParameter[] $queryParameters */
        $queryParameters = Util::findAnnotations($methodAnnotations, QueryParameter::class);

        $rules = [];
        foreach ($queryParameters as $parameter) {
            if ($parameter->rules) {
                $rules[$parameter->name] = $parameter->rules;
            }
        }

        /** @var RequestBody $body */
        $body = Util::findAnnotations($methodAnnotations, RequestBody::class)[0] ?? null;
        if ($body) {
            if ($body->_content instanceof JsonContent) {
                if (is_array($body->_content->properties)) {
                    foreach ($body->_content->properties as $property) {
                        if ($property instanceof Property) {
                            if ($property->rules) {
                                $rules[$property->property] = $property->rules;
                            }
                        }
                    }
                }
            }
        }

        return static::$rules[$class][$method] = $rules;
    }
}
