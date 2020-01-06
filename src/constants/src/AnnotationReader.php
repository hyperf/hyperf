<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
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
            if ($docComment) {
                $result[$code] = $this->parse($docComment);
            }
        }

        return $result;
    }

    protected function parse(string $doc)
    {
        $pattern = '/\\@(\\w+)\\(\\"(.+)\\"\\)/U';
        if (preg_match_all($pattern, $doc, $result)) {
            if (isset($result[1], $result[2])) {
                $keys = $result[1];
                $values = $result[2];

                $result = [];
                foreach ($keys as $i => $key) {
                    if (isset($values[$i])) {
                        $result[Str::lower($key)] = $values[$i];
                    }
                }
                return $result;
            }
        }

        return [];
    }
}
