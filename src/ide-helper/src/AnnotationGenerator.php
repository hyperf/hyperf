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
namespace Hyperf\IDEHelper;

use Hyperf\IDEHelper\Visitor\AnnotationIDEVisitor;
use Hyperf\Support\Composer;
use ReflectionClass;

class AnnotationGenerator
{
    /**
     * @var string
     */
    protected $output;

    /**
     * @var Ast
     */
    protected $ast;

    public function __construct(?string $output = null)
    {
        $this->output = $output ?: __DIR__ . '/../output';
        $this->ast = new Ast();
    }

    public function generate(): void
    {
        $classMap = Composer::getLoader()->getClassMap();
        foreach ($classMap as $value => $path) {
            if (str_starts_with($value, 'Hyperf\\')) {
                try {
                    $reflection = new ReflectionClass($value);
                    $attributes = $reflection->getAttributes();
                    foreach ($attributes as $attribute) {
                        if ($attribute->getName() === 'Attribute') {
                            $code = $this->ast->generate($reflection, file_get_contents($path), [
                                AnnotationIDEVisitor::class,
                            ]);

                            $target = $this->output . '/' . str_replace('\\', '/', $reflection->getName());
                            if (! is_dir(dirname($target))) {
                                mkdir(dirname($target), 0777, true);
                            }

                            file_put_contents($target . '.php', $code);
                        }
                    }
                } catch (\Throwable) {
                }
            }
        }
    }
}
