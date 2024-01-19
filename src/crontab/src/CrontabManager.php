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

use InvalidArgumentException;

class CrontabManager
{
    public const ROUTE = BASE_PATH . '/config/crontab.php';

    /**
     * @var array<string, Crontab>
     */
    protected array $crontabs = [];

    /**
     * @var array<Crontab>
     */
    protected static array $pendingCrontabs = [];

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
     * @return array<Crontab>
     * @throws InvalidArgumentException
     */
    public function parse(): array
    {
        $result = [];
        $crontabs = $this->getCrontabs();
        $last = time();
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

    public static function loadPendingCrontabs(): void
    {
        if (is_file(self::ROUTE)) {
            require_once self::ROUTE;
        }
    }

    public static function addPendingCrontab(Crontab $crontab): void
    {
        static::$pendingCrontabs[] = $crontab;
    }

    /**
     * @return array<Crontab>
     */
    public static function getPendingCrontabs(): array
    {
        return static::$pendingCrontabs;
    }
}
