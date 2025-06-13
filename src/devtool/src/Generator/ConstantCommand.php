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
use InvalidArgumentException;
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
        $this->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Constant type, const or enum', '');
        parent::configure();
    }

    public function getType(): string
    {
        return (string) $this->input->getOption('type');
    }

    protected function getStub(): string
    {
        $type = $this->getType();
        if (! $type) {
            return $this->getConfig()['stub'] ?? __DIR__ . '/stubs/constant_enum.stub';
        }

        $stubs = array_merge(
            ['const' => __DIR__ . '/stubs/constant.stub', 'enum' => __DIR__ . '/stubs/constant_enum.stub'],
            $this->getConfig()['stubs'] ?? []
        );

        if (! isset($stubs[$type])) {
            throw new InvalidArgumentException('The type of constant is not exists.');
        }

        return $stubs[$type];
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\Constants';
    }
}
