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
namespace Hyperf\Devtool\Adapter;

use Hyperf\Di\Annotation\AspectCollector;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Aspects extends AbstractAdapter
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->prepareResult();
        $this->dump($result, $output);
    }

    /**
     * Prepare the result, maybe this result not just use in here.
     */
    public function prepareResult(): array
    {
        $result = [];
        $aspects = AspectCollector::list();
        foreach ($aspects as $type => $collections) {
            foreach ($collections as $aspect => $target) {
                $result[$aspect][$type] = $target;
            }
        }
        return $result;
    }

    /**
     * Dump to the console according to the prepared result.
     */
    private function dump(array $result, OutputInterface $output): void
    {
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
