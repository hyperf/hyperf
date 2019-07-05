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

namespace Hyperf\Crontab\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab as CrontabAnnotation;
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\CrontabManager;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Process\Annotation\Process;
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
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        $crontabs = value(function () {
            $configCrontabs = $this->config->get('crontab.crontab', []);
            $annotationCrontabs = AnnotationCollector::getClassByAnnotation(CrontabAnnotation::class);
            $crontabs = [];
            foreach (array_merge($configCrontabs, $annotationCrontabs) as $crontab) {
                if ($crontab instanceof CrontabAnnotation) {
                    $instance = new Crontab();
                    isset($crontab->name) && $instance->setName($crontab->name);
                    isset($crontab->type) && $instance->setType($crontab->type);
                    isset($crontab->rule) && $instance->setRule($crontab->rule);
                    isset($crontab->callback) && $instance->setCallback($crontab->callback);
                    isset($crontab->memo) && $instance->setMemo($crontab->memo);
                    $crontab = $instance;
                }
                if ($crontab instanceof Crontab) {
                    $crontabs[$crontab->getName()] = $crontab;
                }
            }
            return array_values($crontabs);
        });
        foreach ($crontabs as $crontab) {
            if ($crontab instanceof Crontab) {
                $this->logger->debug(sprintf('Crontab %s have been registered.', $crontab->getName()));
                $this->crontabManager->register($crontab);
            }
        }
    }
}
