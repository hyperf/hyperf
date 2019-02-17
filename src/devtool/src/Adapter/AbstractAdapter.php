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
