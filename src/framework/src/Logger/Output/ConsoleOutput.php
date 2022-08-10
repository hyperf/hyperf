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
namespace Hyperf\Framework\Logger\Output;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\Output;

class ConsoleOutput extends Output
{
    public function __construct(?int $verbosity = self::VERBOSITY_NORMAL, bool $decorated = true, OutputFormatterInterface $formatter = null)
    {
        parent::__construct($verbosity, $decorated, $formatter);
    }

    public function doWrite(string $message, bool $newline)
    {
        if ($newline) {
            $message .= \PHP_EOL;
        }
        echo $message;
    }
}
