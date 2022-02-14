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
use PhpParser\NodeVisitorAbstract;

abstract class AbstractVisitor extends NodeVisitorAbstract
{
    /**
     * @var ModelOption
     */
    protected $option;

    /**
     * @var ModelData
     */
    protected $data;

    public function __construct(ModelOption $option, ModelData $data)
    {
        $this->option = $option;
        $this->data = $data;
    }
}
