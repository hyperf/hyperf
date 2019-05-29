<?php

namespace Hyperf\Command;

use Closure;

trait ConfirmableTrait
{
    /**
     * Confirm before proceeding with the action.
     *
     * This method only asks for confirmation in production.
     *
     * @param  string  $warning
     * @param  \Closure|bool|null  $callback
     * @return bool
     */
    public function confirmToProceed($warning = 'Application In Production!', $callback = false)
    {
        $shouldConfirm = $callback instanceof Closure ? call_user_func($callback) : $callback;

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

}
