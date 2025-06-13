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

use BackedEnum;
use Hyperf\Constants\Annotation\Message;
use Hyperf\Stringable\Str;
use ReflectionClassConstant;

class AnnotationReader
{
    /**
     * @param array<ReflectionClassConstant> $classConstants
     */
    public function getAnnotations(array $classConstants): array
    {
        $result = [];
        foreach ($classConstants as $classConstant) {
            $code = $classConstant->getValue();
            if ($classConstant->isEnumCase()) {
                $code = $code instanceof BackedEnum ? $code->value : $code->name;
            }

            $docComment = $classConstant->getDocComment();
            // Not support float and bool, because it will be convert to int.
            if ($docComment && (is_int($code) || is_string($code))) {
                $result[$code] = $this->parse($docComment, $result[$code] ?? []);
            }

            // Support PHP8 Attribute.
            foreach ($classConstant->getAttributes() as $ref) {
                $attribute = $ref->newInstance();
                if ($attribute instanceof Message) {
                    $result[$code][$attribute->getLowerCaseKey()] = $attribute->value;
                }
            }
        }

        return $result;
    }

    protected function parse(string $doc, array $previous): array
    {
        $pattern = '/@(\w+)\("(.+)"\)/U';
        if (preg_match_all($pattern, $doc, $result)) {
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
