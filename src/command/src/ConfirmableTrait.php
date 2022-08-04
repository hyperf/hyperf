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
namespace Hyperf\Command;

trait ConfirmableTrait
{
    /**
     * Confirm before proceeding with the action.
     *
     * This method only asks for confirmation in production.
     *
     * @param string $warning
     * @param null|bool|\Closure $callback
     * @return bool
     */
    public function confirmToProceed($warning = 'Application In Production!', $callback = null)
    {
        $callback ??= $this->getDefaultConfirmCallback();

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

    /**
     * Get the default confirmation callback.
     *
     * @return \Closure
     */
    protected function getDefaultConfirmCallback()
    {
        return fn () => (
            is_callable(['Composer\InstalledVersions', 'getRootPackage'])
                && \Composer\InstalledVersions::getRootPackage()['dev'] === false
        );
    }
}
