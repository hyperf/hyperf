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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractAdapter
{
    abstract public function execute(InputInterface $input, OutputInterface $output);

    protected function tab(string $append = '', int $int = 1, int $length = 4)
    {
        return str_repeat(' ', $int * $length) . $append;
    }
}
