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

use Closure;
use Composer\InstalledVersions;

use function Hyperf\Support\value;

trait Confirmable
{
    /**
     * Confirm before proceeding with the action.
     *
     * This method only asks for confirmation in production.
     */
    public function confirmToProceed(string $warning = 'Application In Production!', null|bool|Closure $callback = null): bool
    {
        $callback ??= $this->isShouldConfirm();

        $shouldConfirm = value($callback);

        if ($shouldConfirm) {
            if ($this->input->getOption('force')) {
                return true;
            }

            $this->alert($warning);

            $confirmed = $this->confirm('Do you really wish to run this command?');

            if (! $confirmed) {
                $this->comment('Command Cancelled!');

                return false;
            }
        }

        return true;
    }

    protected function isShouldConfirm(): bool
    {
        return is_callable(['Composer\InstalledVersions', 'getRootPackage'])
            && (InstalledVersions::getRootPackage()['dev'] ?? false) === false;
    }
}
