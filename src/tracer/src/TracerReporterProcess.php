<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Tracer;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;

/**
 * Class Process.
 * @Process
 */
class TracerReporterProcess extends AbstractProcess
{
    public $name = 'tracer-reporter';

    public $nums = 2;

    /**
     * The logical of process will place in here.
     */
    public function handle(): void
    {
    }
}
