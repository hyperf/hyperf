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

namespace Hyperf\ViewEngine\Command;

use Hyperf\Command\Command as HyperfCommand;

class ViewPublishCommand extends HyperfCommand
{
    protected ?string $signature = 'view:publish {--f|force}';

    protected array $packages = [
        'hyperf/session',
        'hyperf/validation',
        'hyperf/translation',
    ];

    public function handle()
    {
        $this->call('vendor:publish', [
            'package' => 'hyperf/view-engine',
            '--force' => true,
        ]);

        foreach ($this->packages as $package) {
            $this->call('vendor:publish', [
                'package' => $package,
                '--force' => ! ($this->input->getOption('force') === false),
            ]);
        }
    }
}
