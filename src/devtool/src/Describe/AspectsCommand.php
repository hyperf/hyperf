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

namespace Hyperf\Devtool\Describe;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Di\Annotation\AspectCollector;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[Command]
class AspectsCommand extends HyperfCommand
{
    public function __construct()
    {
        parent::__construct('describe:aspects');
    }

    public function handle()
    {
        $classes = $this->input->getOption('classes');
        $classes = $classes ? explode(',', $classes) : null;
        $aspects = $this->input->getOption('aspects');
        $aspects = $aspects ? explode(',', $aspects) : null;

        $collector = AspectCollector::list();
        $this->show('Classes', $this->handleData($collector['classes'], $classes, $aspects), $this->output);
        $this->show('Annotations', $this->handleData($collector['annotations'], $classes, $aspects), $this->output);
    }

    protected function configure()
    {
        $this->setDescription('Describe the aspects.')
            ->addOption('classes', 'e', InputOption::VALUE_OPTIONAL, 'Get the detail of the specified information by classes.')
            ->addOption('aspects', 'l', InputOption::VALUE_OPTIONAL, 'Get the detail of the specified information by aspects.');
    }

    protected function handleData(array $collector, ?array $classes, ?array $aspects): array
    {
        $data = [];
        foreach ($collector as $aspect => $targets) {
            foreach ($targets as $target) {
                if ($classes && ! $this->isMatch($target, $classes)) {
                    continue;
                }
                if ($aspects && ! $this->isMatch($aspect, $aspects)) {
                    continue;
                }
                $data[$target]['targets'] = $target;
                $data[$target]['aspects'] = array_merge($data[$target]['aspects'] ?? [], [$aspect]);
            }
        }
        return $data;
    }

    protected function isMatch(string $target, array $keywords = [])
    {
        foreach ($keywords as $keyword) {
            if (str_contains($target, $keyword)) {
                return true;
            }
        }
        return false;
    }

    protected function show(string $title, array $data, OutputInterface $output)
    {
        $rows = [];
        foreach ($data as $route) {
            $route['aspects'] = implode(PHP_EOL, (array) $route['aspects']);
            $rows[] = $route;
            $rows[] = new TableSeparator();
        }
        $rows = array_slice($rows, 0, count($rows) - 1);
        if ($rows) {
            $table = new Table($output);
            $table->setHeaders([$title, 'Aspects'])->setRows($rows);
            $table->render();
        }
    }
}
