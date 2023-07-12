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
namespace Hyperf\Command\Concerns;

trait InjectWithInputAndOutput
{
    /**
     * @var null|\Symfony\Component\Console\Input\Input
     */
    private $input;

    /**
     * @var null|\Symfony\Component\Console\Style\SymfonyStyle
     */
    private $output;
}
