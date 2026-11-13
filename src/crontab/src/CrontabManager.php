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

namespace Hyperf\Crontab;

class CrontabManager
{
    /**
     * @var array<string, Crontab>
     */
    protected array $crontabs = [];

    public function __construct(protected Parser $parser)
    {
    }

    public function register(Crontab $crontab): bool
    {
        if (! $this->isValidCrontab($crontab) || ! $crontab->isEnable()) {
            return false;
        }
        $this->crontabs[$crontab->getName()] = $crontab;
        return true;
    }

    /**
     * @return Crontab[]
     */
    public function parse(): array
    {
        $result = [];
        $crontabs = $this->getCrontabs();
        // Parse schedules from the beginning of the current minute. Using the
        // current second as the base shifts second-level rules (e.g. */5) by
        // that second and causes already elapsed slots to execute immediately.
        $last = (int) (time() / 60) * 60;
        foreach ($crontabs as $key => $crontab) {
            if (! $crontab instanceof Crontab) {
                unset($this->crontabs[$key]);
                continue;
            }
            $time = $this->parser->parse($crontab->getRule(), $last, $crontab->getTimezone());
            if ($time) {
                foreach ($time as $t) {
                    $result[] = (clone $crontab)->setExecuteTime($t);
                }
            }
        }
        return $result;
    }

    /**
     * @return array<string, Crontab>
     */
    public function getCrontabs(): array
    {
        return $this->crontabs;
    }

    public function isValidCrontab(Crontab $crontab): bool
    {
        return $crontab->getName() && $crontab->getRule() && $crontab->getCallback() && $this->parser->isValid($crontab->getRule());
    }
}
