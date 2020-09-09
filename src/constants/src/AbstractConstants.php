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

use Hyperf\Constants\Exception\ConstantsException;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Str;

abstract class AbstractConstants
{
    public static function __callStatic($name, $arguments)
    {
        if (! Str::startsWith($name, 'get')) {
            throw new ConstantsException('The function is not defined!');
        }

        if (! isset($arguments) || count($arguments) === 0) {
            throw new ConstantsException('The Code is required');
        }

        $code = $arguments[0];
        $name = strtolower(substr($name, 3));
        $class = get_called_class();

        $message = ConstantsCollector::getValue($class, $code, $name);

        array_shift($arguments);

        $result = self::translate($message, $arguments);
        // If the result of translate doesn't exist, the result is equal with message, so we will skip it.
        if ($result && $result !== $message) {
            return $result;
        }

        $count = count($arguments);
        if ($count > 0) {
            return sprintf($message, ...(array) $arguments[0]);
        }

        return $message;
    }

    protected static function translate($key, $arguments): ?string
    {
        if (! ApplicationContext::hasContainer() || ! ApplicationContext::getContainer()->has(TranslatorInterface::class)) {
            return null;
        }

        $replace = $arguments[0] ?? [];
        if (! is_array($replace)) {
            return null;
        }

        $translator = ApplicationContext::getContainer()->get(TranslatorInterface::class);

        return $translator->trans($key, $replace);
    }
}
