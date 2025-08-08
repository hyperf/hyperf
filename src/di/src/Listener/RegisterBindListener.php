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

namespace Hyperf\Di\Listener;

use Hyperf\Contract\ContainerInterface as ContainerPlusInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Bind as BindAnnotation;
use Hyperf\Di\Annotation\BindTo as BindToAnnotation;
use Hyperf\Di\Annotation\MultipleAnnotation;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Psr\Container\ContainerInterface;

class RegisterBindListener implements ListenerInterface
{
    /**
     * @param ContainerPlusInterface $container
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly StdoutLoggerInterface $logger
    ) {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        if (! $this->container instanceof ContainerPlusInterface) {
            $this->logger->error(sprintf('[di] Bind registered failed, because the container cannot implements %s', ContainerPlusInterface::class));
            return;
        }
        $this->registerBind();
        $this->registerBindTo();
        $this->logger->debug(sprintf('[di] Bind registered by %s', self::class));
    }

    private function registerBind(): void
    {
        $binds = AnnotationCollector::getClassesByAnnotation(BindAnnotation::class);
        foreach ($binds as $class => $metadata) {
            /** @var MultipleAnnotation $metadata */
            /** @var BindAnnotation[] $annotations */
            $annotations = $metadata->toAnnotations();
            foreach ($annotations as $annotation) {
                $this->container->define($class, $annotation->getValue());
            }
        }
    }

    private function registerBindTo(): void
    {
        $binds = AnnotationCollector::getClassesByAnnotation(BindToAnnotation::class);
        foreach ($binds as $class => $metadata) {
            /** @var MultipleAnnotation $metadata */
            /** @var BindToAnnotation[] $annotations */
            $annotations = $metadata->toAnnotations();
            foreach ($annotations as $annotation) {
                $this->container->define($annotation->getValue(), $class);
            }
        }
    }
}
