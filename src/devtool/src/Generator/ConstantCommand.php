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

namespace Hyperf\Devtool\Generator;

use Hyperf\Command\Annotation\Command;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class ConstantCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('gen:constant');
    }

    public function configure()
    {
        $this->setDescription('Create a new constant class');
        $this->addOption('type', 'type', InputOption::VALUE_OPTIONAL, 'Constant type, default is class,e.g.class,enum', 'class');
        parent::configure();
    }

    public function getType(): string
    {
        $type = $this->input->getOption('type');
        if (! in_array($type, ['class', 'enum'], true)) {
            $type = 'class';
        }
        return $type;
    }

    protected function getStub(): string
    {
        if ($this->getConfig()['stub']) {
            return $this->getConfig()['stub'];
        }
        return $this->getType() === 'class' ? __DIR__ . '/stubs/constant.stub' : __DIR__ . '/stubs/constant_enum.stub';
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Constants';
    }
}
