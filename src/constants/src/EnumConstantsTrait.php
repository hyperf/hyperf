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
use Hyperf\Constants\Exception\ConstantsException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnitEnum;

use function is_subclass_of;

/**
 * @method string getMessage(array $translate = null)
 */
trait EnumConstantsTrait
{
    use GetterTrait;

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception\ConstantsException
     * @throws NotFoundExceptionInterface
     */
    public function __call(string $name, array $arguments): string|array
    {
        $code = match (true) {
            $this instanceof BackedEnum => $this->value,
            $this instanceof UnitEnum => $this->name,
            default => throw new ConstantsException('This trait must in enum'),
        };
        return static::getValue($name, [$code, ...$arguments]);
    }

    /**
     * @throws Exception\ConstantsException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function __callStatic(string $name, array $arguments): string|array
    {
        if (! is_subclass_of(static::class, UnitEnum::class)) {
            throw new ConstantsException('This trait must in enum');
        }
        if (! empty($arguments)) {
            if ($arguments[0] instanceof BackedEnum) {
                $arguments[0] = $arguments[0]->value;
            } elseif ($arguments[0] instanceof UnitEnum) {
                $arguments[0] = $arguments[0]->name;
            }
        }
        return static::getValue($name, $arguments);
    }
}
