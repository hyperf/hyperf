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

class ValidationCollector
{
    protected static array $data = [];

    /**
     * @param string $field rules or attribute
     */
    public static function get(string $class, string $method, string $field): array
    {
        if (! empty(static::$data[$class][$method][$field])) {
            return static::$data[$class][$method][$field];
        }

        $methodAnnotations = AnnotationCollector::getClassMethodAnnotation($class, $method);

        $data = [];
        $data = self::collectQueryParameter($methodAnnotations, $data, $field);
        $data = self::collectJsonContentRequestBody($methodAnnotations, $data, $field);
        $data = self::collectMediaTypeRequestBody($methodAnnotations, $data, $field);

        return static::$data[$class][$method][$field] = $data;
    }

    protected static function collectQueryParameter($methodAnnotations, array $data, string $field): array
    {
        /** @var QueryParameter[] $queryParameters */
        $queryParameters = Util::findAnnotations($methodAnnotations, QueryParameter::class);

        foreach ($queryParameters as $property) {
            if (isset($property->{$field}) && $property->{$field}) {
                $data[$property->name] = $property->{$field};
            }
        }
        return $data;
    }

    protected static function collectJsonContentRequestBody($methodAnnotations, array $data, string $field): array
    {
        /** @var null|RequestBody $body */
        $body = Util::findAnnotations($methodAnnotations, RequestBody::class)[0] ?? null;
        if (! $body) {
            return $data;
        }

        if (! $body->_content instanceof JsonContent) {
            return $data;
        }

        if (! is_array($body->_content->properties)) {
            return $data;
        }

        foreach ($body->_content->properties as $property) {
            if ($property instanceof Property) {
                if (isset($property->{$field}) && $property->{$field}) {
                    $data[$property->property] = $property->{$field};
                }
            }
        }

        return $data;
    }

    protected static function collectMediaTypeRequestBody($methodAnnotations, array $data, string $field): array
    {
        /** @var null|RequestBody $body */
        $body = Util::findAnnotations($methodAnnotations, RequestBody::class)[0] ?? null;
        if (! $body || ! is_array($body->content)) {
            return $data;
        }

        foreach ($body->content as $content) {
            if (! $content instanceof MediaType) {
                continue;
            }

            /* @phpstan-ignore-next-line */
            if (! $content->schema || ! is_array($content->schema->properties)) {
                continue;
            }
            foreach ($content->schema->properties as $property) {
                if (! $property instanceof Property) {
                    continue;
                }
                if (isset($property->{$field}) && $property->{$field}) {
                    $data[$property->property] = $property->{$field};
                }
            }
        }
        return $data;
    }
}
