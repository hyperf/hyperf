<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Devtool\Adapter;

use Hyperf\Di\Annotation\AspectCollector;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Aspects extends AbstractAdapter
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $result = [];
        $aspects = AspectCollector::getContainer();
        foreach ($aspects as $type => $collections) {
            foreach ($collections as $aspect => $target) {
                $result[$aspect][$type] = $target;
            }
        }
        foreach ($result as $aspect => $targets) {
            $output->writeln("<info>{$aspect}</info>");
            if (isset($targets['annotations'])) {
                $output->writeln($this->tab('Annotations:'));
                foreach ($targets['annotations'] ?? [] as $annotation) {
                    $output->writeln($this->tab($annotation ?? '', 2));
                }
            }
            if (isset($targets['classes'])) {
                $output->writeln($this->tab('Classes:'));
                foreach ($targets['classes'] ?? [] as $class) {
                    $output->writeln($this->tab($class ?? '', 2));
                }
            }
        }
    }
}
