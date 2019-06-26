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

namespace Hyperf\Crontab;

class CrontabManager
{
    /**
     * @var Crontab[]
     */
    protected $crontabs = [];

    /**
     * @var Parser
     */
    protected $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function register(Crontab $crontab): bool
    {
        if (! $this->isValidCrontab($crontab)) {
            return false;
        }
        $this->crontabs[] = $crontab;
        return true;
    }

    public function parse(int $start = null): array
    {
        $result = [];
        $crontabs = $this->getCrontabs();
        foreach ($crontabs ?? [] as $key => $crontab) {
            if (! $crontab instanceof Crontab) {
                unset($this->crontabs[$key]);
                continue;
            }
            $time = $this->parser->parse($crontab->rule, $start);
            $result[spl_object_hash($crontab)] = [
                'crontab' => $crontab,
                'time' => $time,
            ];
        }
        return $result;
    }

    public function getCrontabs(): array
    {
        return $this->crontabs;
    }

    private function isValidCrontab(Crontab $crontab): bool
    {
        return isset($crontab->name, $crontab->rule, $crontab->command) && $this->parser->isValid($crontab->rule);
    }
}
