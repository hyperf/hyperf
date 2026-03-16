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

namespace Hyperf\Carbon\Listener;

use Carbon\Carbon as BaseCarbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Hyperf\Carbon\Carbon;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Psr\Container\ContainerInterface;

class CarbonListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $locale = $this->getLocale();

        if ($locale === null) {
            return;
        }

        BaseCarbon::setLocale($locale);
        CarbonImmutable::setLocale($locale);
        CarbonPeriod::setLocale($locale);
        CarbonInterval::setLocale($locale);
        Carbon::setLocale($locale);
    }

    protected function getLocale(): ?string
    {
        return match (true) {
            $this->container->has(TranslatorInterface::class) => $this->container->get(TranslatorInterface::class)->getLocale(),
            default => null,
        };
    }
}
