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
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\TranslatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function array_shift;
use function is_array;
use function sprintf;
use function strtolower;
use function substr;

trait GetterTrait
{
    /**
     * @throws ConstantsException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function getValue(string $name, array $arguments): array|string
    {
        if (! str_starts_with($name, 'get')) {
            throw new ConstantsException("The function {$name} is not defined!");
        }

        if (empty($arguments)) {
            throw new ConstantsException('The Code is required');
        }

        $code = array_shift($arguments);
        $name = strtolower(substr($name, 3));

        $message = ConstantsCollector::getValue(static::class, $code, $name);

        $result = self::translate($message, $arguments);
        // If the result of translate doesn't exist, the result is equal with message, so we will skip it.
        if ($result && $result !== $message) {
            return $result;
        }

        if (! empty($arguments)) {
            return sprintf($message, ...(array) $arguments[0]);
        }

        return $message;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected static function translate(string $key, array $arguments): null|array|string
    {
        if (! ApplicationContext::hasContainer() || ! ApplicationContext::getContainer()->has(TranslatorInterface::class)) {
            return null;
        }

        $replace = array_shift($arguments) ?? [];
        if (! is_array($replace)) {
            return null;
        }

        $translator = ApplicationContext::getContainer()->get(TranslatorInterface::class);

        return $translator->trans($key, $replace);
    }
}
