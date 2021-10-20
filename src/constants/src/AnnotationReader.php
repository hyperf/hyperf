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
namespace Hyperf\Constants;

use Hyperf\Utils\Str;
use ReflectionClassConstant;

class AnnotationReader
{
    public function getAnnotations(array $classConstants)
    {
        $result = [];
        /** @var ReflectionClassConstant $classConstant */
        foreach ($classConstants as $classConstant) {
            $code = $classConstant->getValue();
            $docComment = $classConstant->getDocComment();
            // Not support float and bool, because it will be convert to int.
            if ($docComment && (is_int($code) || is_string($code))) {
                $result[$code] = $this->parse($docComment, $result[$code] ?? []);
            }
        }

        return $result;
    }

    /**
     * Get the doc element matched
     * @param string $doc
     * @param array $previous
     * @return array
     */
    protected function parse(string $doc, array $previous = [])
    {
        $patternDoubleQuota = '/\\@(\\w+)\\(\\"(.+)\\"\\)/U';
        $patternSingleQuota = '/\\@(\\w+)\\(\'(.+)\'\\)/U';
        if (preg_match_all($patternDoubleQuota, $doc, $result)) {
            $previous = $this->parseDoc($result);
        }
        if (preg_match_all($patternSingleQuota, $doc, $result)){
            $previous = array_merge($this->parseDoc($result), $previous);
        }

        return $previous;
    }

    /**
     * @param array $result
     * @return array
     */
    protected function parseDoc(array $result = [])
    {
        $previous = [];
        if (isset($result[1], $result[2])) {
            $keys = $result[1];
            $values = $result[2];

            foreach ($keys as $i => $key) {
                if (isset($values[$i])) {
                    $previous[Str::lower($key)] = $values[$i];
                }
            }
        }

        return $previous;
    }
}
