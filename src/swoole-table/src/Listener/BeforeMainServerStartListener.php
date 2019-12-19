<?php
declare(strict_types=1);

/**
 *
 * @author zhenguo.guan
 * @date 2019-12-19 10:18
 */

namespace Hyperf\SwooleTable\Listener;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeMainServerStart;
use Hyperf\SwooleTable\Annotation\SwooleTable;
use Hyperf\SwooleTable\SwooleTableInterface;
use Hyperf\SwooleTable\SwooleTableManager;
use Psr\Container\ContainerInterface;

class BeforeMainServerStartListener implements ListenerInterface
{


    protected $swooleTableManager;

    /**
     * BeforeMainServerStartListener constructor.
     * @param ContainerInterface $container
     * @author zhenguo.guan
     */
    public function __construct(ContainerInterface $container)
    {
        $this->swooleTableManager = $container->get(SwooleTableManager::class);
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BeforeMainServerStart::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        $classes = AnnotationCollector::getClassByAnnotation(SwooleTable::class);
        foreach ($classes as $class => $annotation) {
            $instance = make($class);
            if (! $instance instanceof SwooleTableInterface) {
                continue;
            }

            $table = $instance->create();
            $this->swooleTableManager->add($class, $table);
        }
    }
}