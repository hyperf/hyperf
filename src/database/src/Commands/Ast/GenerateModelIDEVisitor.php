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
namespace Hyperf\Database\Commands\Ast;

use Hyperf\Database\Commands\ModelData;
use Hyperf\Database\Commands\ModelOption;
use Hyperf\Database\Model\Builder;
use Hyperf\Utils\Str;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;

class GenerateModelIDEVisitor extends AbstractVisitor
{
    /**
     * @var array
     */
    protected $methods = [];

    public function __construct(ModelOption $option, ModelData $data)
    {
        parent::__construct($option, $data);

        $this->initPropertiesFromMethods();
    }

    protected function setMethod(string $name, array $type = [], array $arguments = [])
    {
        $methods = array_change_key_case($this->methods, CASE_LOWER);

        if (! isset($methods[strtolower($name)])) {
            $this->methods[$name] = [];
            $this->methods[$name]['type'] = implode('|', $type);
            $this->methods[$name]['arguments'] = $arguments;
        }
    }

    protected function initPropertiesFromMethods()
    {
        /** @var ReflectionClass $reflection */
        $reflection = BetterReflectionManager::getReflector()->reflect($this->data->getClass());
        $methods = $reflection->getImmediateMethods();

        sort($methods);
        /** @var ReflectionMethod $method */
        foreach ($methods as $method) {
            if (Str::startsWith($method->getName(), 'scope') && $method->getName() !== 'scopeQuery') {
                $name = Str::camel(substr($method->getName(), 5));
                if (! empty($name)) {
                    $args = $method->getParameters();
                    // Remove the first ($query) argument
                    array_shift($args);
                    $this->setMethod($name, [Builder::class, $method->getDeclaringClass()->getName()], $args);
                }
                continue;
            }

            if ($method->getNumberOfParameters() > 0) {
                continue;
            }
        }
    }
}
