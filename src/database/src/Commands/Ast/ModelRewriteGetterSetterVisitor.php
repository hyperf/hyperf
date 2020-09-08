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
use Roave\BetterReflection\Reflection\ReflectionClass;

class ModelRewriteGetterSetterVisitor extends AbstractVisitor
{
    protected $methods = [];

    public function __construct(ModelOption $option, ModelData $data)
    {
        parent::__construct($option, $data);

        $this->collectMethods($data->getClass());
    }

    protected function collectMethods(string $class)
    {
        // /** @var ReflectionClass $reflection */
        // $reflection = self::getReflector()->reflect($class);
        // $methods = $reflection->getImmediateMethods();
        // foreach ($methods as $method) {
        //     var_dump($method);
        // }
    }
}
