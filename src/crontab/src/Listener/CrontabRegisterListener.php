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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab as CrontabAnnotation;
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\CrontabManager;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\ReflectionManager;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Process\Event\BeforeCoroutineHandle;
use Hyperf\Process\Event\BeforeProcessHandle;

class CrontabRegisterListener implements ListenerInterface
{
    /**
     * @var \Hyperf\Crontab\CrontabManager
     */
    protected $crontabManager;

    /**
     * @var \Hyperf\Contract\StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var \Hyperf\Contract\ConfigInterface
     */
    private $config;

    public function __construct(CrontabManager $crontabManager, StdoutLoggerInterface $logger, ConfigInterface $config)
    {
        $this->crontabManager = $crontabManager;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BeforeProcessHandle::class,
            BeforeCoroutineHandle::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        $crontabs = $this->parseCrontabs();
        foreach ($crontabs as $crontab) {
            if ($crontab instanceof Crontab && $this->crontabManager->register($crontab)) {
                $this->logger->debug(sprintf('Crontab %s have been registered.', $crontab->getName()));
            }
        }
    }

    private function parseCrontabs(): array
    {
        $configCrontabs = $this->config->get('crontab.crontab', []);
        $annotationCrontabs = AnnotationCollector::getClassesByAnnotation(CrontabAnnotation::class);
        $crontabs = [];
        foreach (array_merge($configCrontabs, $annotationCrontabs) as $className => $crontab) {
            if ($crontab instanceof CrontabAnnotation) {
                $crontab = $this->buildCrontabByAnnotation($className, $crontab);
            }
            if ($crontab instanceof Crontab) {
                $crontabs[$crontab->getName()] = $crontab;
            }
        }
        return array_values($crontabs);
    }

    private function buildCrontabByAnnotation(string $className, CrontabAnnotation $annotation): Crontab
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
        isset($annotation->enable) && $crontab->setEnable($annotation->enable);

        if ($annotation->enableMethod) {
            $crontab->setEnable($this->resolveCrontabEnableMethod($className, $annotation->enableMethod, $crontab->isEnable()));
        }

        return $crontab;
    }

    private function resolveCrontabEnableMethod(string $className, string $enableMethod, bool $default): bool
    {
        try {
            $reflectionClass = ReflectionManager::reflectClass($className);
            $method = $reflectionClass->getMethod($enableMethod);

            if ($method->isPublic()) {
                return make($className)->{$enableMethod}();
            }
        } catch (\ReflectionException $e) {
        }

        return $default;
    }
}
