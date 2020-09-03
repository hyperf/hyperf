<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Cache\Aspect;

use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\AnnotationManager;
use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Driver\KeyCollectorInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Psr\Container\ContainerInterface;

/**
 * @Aspect
 */
class CacheableAspect extends AbstractAspect
{
    public $classes = [];

    public $annotations = [
        Cacheable::class,
    ];

    /**
     * @var ContainerInterface
     */
    protected $manager;

    /**
     * @var AnnotationManager
     */
    protected $annotationManager;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(CacheManager $manager, AnnotationManager $annotationManager, StdoutLoggerInterface $logger, FormatterInterface $formatter, ConfigInterface $config)
    {
        $this->manager = $manager;
        $this->annotationManager = $annotationManager;
        $this->logger = $logger;
        $this->formatter = $formatter;
        $this->config = $config;
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if($this->config->get('enable_cache', true) == false) {
            return $proceedingJoinPoint->process();
        }

        $className = $proceedingJoinPoint->className;
        $method = $proceedingJoinPoint->methodName;
        $arguments = $proceedingJoinPoint->arguments['keys'];

        [$key, $ttl, $group, $annotation] = $this->annotationManager->getCacheableValue($className, $method, $arguments);

        try {
            $driver = $this->manager->getDriver($group);
            [$has, $result] = $driver->fetch($key);
            if ($has) {
                return $result;
            }
        } catch (\Throwable $e) {
            $this->logger->error($this->formatter->format($e), ['class' => $className, 'method' => $method, 'args' => $arguments]);
            return $proceedingJoinPoint->process();
        }

        $result = $proceedingJoinPoint->process();

        $driver->set($key, $result, $ttl);
        if ($driver instanceof KeyCollectorInterface && $annotation instanceof Cacheable && $annotation->collect) {
            $driver->addKey($annotation->prefix . 'MEMBERS', $key);
        }

        return $result;
    }
}
