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

namespace Hyperf\Crontab\Listener;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab as CrontabAnnotation;
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\CrontabManager;
use Hyperf\Crontab\LoggerInterface;
use Hyperf\Crontab\Schedule;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\ReflectionManager;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use ReflectionException;

class CrontabRegisterListener implements ListenerInterface
{
    protected CrontabManager $crontabManager;

    protected ?PsrLoggerInterface $logger = null;

    protected ConfigInterface $config;

    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event): void
    {
        $this->crontabManager = $this->container->get(CrontabManager::class);
        $this->logger = match (true) {
            $this->container->has(LoggerInterface::class) => $this->container->get(LoggerInterface::class),
            $this->container->has(StdoutLoggerInterface::class) => $this->container->get(StdoutLoggerInterface::class),
            default => null,
        };
        $this->config = $this->container->get(ConfigInterface::class);

        if (! $this->config->get('crontab.enable', false)) {
            return;
        }

        $crontabs = $this->parseCrontabs();
        $environment = (string) $this->config->get('app_env', '');

        foreach ($crontabs as $crontab) {
            if (! $crontab instanceof Crontab) {
                continue;
            }

            if (! $crontab->isEnable()) {
                $this->logger?->warning(sprintf('Crontab %s is disabled.', $crontab->getName()));
                continue;
            }

            if (! $crontab->runsInEnvironment($environment)) {
                $this->logger?->warning(sprintf('Crontab %s is disabled in %s environment.', $crontab->getName(), $environment));
                continue;
            }

            if (! $this->crontabManager->isValidCrontab($crontab)) {
                $this->logger?->warning(sprintf('Crontab %s is invalid.', $crontab->getName()));
                continue;
            }

            if ($this->crontabManager->register($crontab)) {
                $this->logger?->debug(sprintf('Crontab %s have been registered.', $crontab->getName()));
            }
        }
    }

    private function parseCrontabs(): array
    {
        $configCrontabs = $this->config->get('crontab.crontab', []);
        $annotationCrontabs = AnnotationCollector::getClassesByAnnotation(CrontabAnnotation::class);
        $methodCrontabs = $this->getCrontabsFromMethod();

        Schedule::load();
        $pendingCrontabs = Schedule::getCrontabs();

        $crontabs = [];

        foreach (array_merge($configCrontabs, $annotationCrontabs, $methodCrontabs, $pendingCrontabs) as $crontab) {
            if ($crontab instanceof CrontabAnnotation) {
                $crontab = $this->buildCrontabByAnnotation($crontab);
            }
            if ($crontab instanceof Crontab) {
                $crontabs[$crontab->getName()] = $crontab;
            }
        }

        return array_values($crontabs);
    }

    private function getCrontabsFromMethod(): array
    {
        $result = AnnotationCollector::getMethodsByAnnotation(CrontabAnnotation::class);
        $crontabs = [];
        foreach ($result as $item) {
            $crontabs[] = $item['annotation'];
        }
        return $crontabs;
    }

    private function buildCrontabByAnnotation(CrontabAnnotation $annotation): Crontab
    {
        $crontab = new Crontab();
        isset($annotation->name) && $crontab->setName($annotation->name);
        isset($annotation->type) && $crontab->setType($annotation->type);
        isset($annotation->rule) && $crontab->setRule($annotation->rule);
        isset($annotation->singleton) && $crontab->setSingleton($annotation->singleton);
        isset($annotation->mutexPool) && $crontab->setMutexPool($annotation->mutexPool);
        isset($annotation->mutexExpires) && $crontab->setMutexExpires($annotation->mutexExpires);
        isset($annotation->onOneServer) && $crontab->setOnOneServer($annotation->onOneServer);
        isset($annotation->callback) && $crontab->setCallback($annotation->callback);
        isset($annotation->memo) && $crontab->setMemo($annotation->memo);
        isset($annotation->enable) && $crontab->setEnable($this->resolveCrontabEnableMethod($annotation->enable));
        isset($annotation->timezone) && $crontab->setTimezone($annotation->timezone);
        isset($annotation->environments) && $crontab->setEnvironments($annotation->environments);
        isset($annotation->options) && $crontab->setOptions($annotation->options);

        return $crontab;
    }

    private function resolveCrontabEnableMethod(array|bool $enable): bool
    {
        if (is_bool($enable)) {
            return $enable;
        }

        $className = reset($enable);
        $method = end($enable);

        try {
            $reflectionClass = ReflectionManager::reflectClass($className);
            $reflectionMethod = $reflectionClass->getMethod($method);

            if ($reflectionMethod->isPublic()) {
                if ($reflectionMethod->isStatic()) {
                    return $className::$method();
                }

                $container = ApplicationContext::getContainer();
                if ($container->has($className)) {
                    return $container->get($className)->{$method}();
                }
            }

            $this->logger?->info('Crontab enable method is not public, skip register.');
        } catch (ReflectionException $e) {
            $this->logger?->error('Resolve crontab enable failed, skip register.' . $e);
        }

        return false;
    }
}
