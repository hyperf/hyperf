<?php
/**
 * RequestCommand.php
 *
 * Author: wangyi <chunhei2008@qq.com>
 *
 * Date:   2019-07-26 20:59
 * Copyright: (C) 2014, Guangzhou YIDEJIA Network Technology Co., Ltd.
 */

namespace Hyperf\Validation\Request;

use Hyperf\Command\Annotation\Command;
use Hyperf\Devtool\Generator\GeneratorCommand;


/**
 * Class RequestCommand
 * @package Hyperf\Validation\Request
 * @Command
 */
class RequestCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('gen:request');
        $this->setDescription('Create a new form request class');
    }

    protected function getStub(): string
    {
        return $this->getConfig()['stub'] ?? __DIR__ . '/stubs/request.stub';
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Requests';
    }
}