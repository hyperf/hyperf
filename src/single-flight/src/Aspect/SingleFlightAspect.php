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

namespace Hyperf\SingleFlight\Aspect;

use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\AnnotationException;
use Hyperf\Di\Exception\Exception;
use Hyperf\SingleFlight\Annotation\SingleFlight as SingleFlightAnnotation;
use Hyperf\SingleFlight\Exception\SingleFlightException;
use Hyperf\SingleFlight\SingleFlight;
use Hyperf\Stringable\Str;
use Throwable;

use function Hyperf\Collection\data_get;

class SingleFlightAspect extends AbstractAspect
{
    public array $annotations = [
        SingleFlightAnnotation::class,
    ];

    /**
     * @throws Exception
     * @throws AnnotationException
     * @throws Throwable
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->shouldHijack($proceedingJoinPoint)) {
            return $proceedingJoinPoint->process();
        }

        $barrierKey = $this->barrierKey($proceedingJoinPoint);

        return $this->shareCall($barrierKey, $proceedingJoinPoint);
    }

    private function shouldHijack(ProceedingJoinPoint $proceedingJoinPoint): bool
    {
        if (! Coroutine::inCoroutine()) {
            return false;
        }

        $annotation = $this->singleFlightAnnotation($proceedingJoinPoint->className, $proceedingJoinPoint->methodName);

        return (bool) $annotation?->value;
    }

    private function singleFlightAnnotation(string $class, string $method): ?SingleFlightAnnotation
    {
        return AnnotationCollector::getClassMethodAnnotation($class, $method)[SingleFlightAnnotation::class] ?? null;
    }

    /**
     * Generate barrier key.
     * @throws AnnotationException
     */
    private function barrierKey(ProceedingJoinPoint $proceedingJoinPoint): string
    {
        $class = $proceedingJoinPoint->className;
        $method = $proceedingJoinPoint->methodName;
        $arguments = $proceedingJoinPoint->arguments['keys'];
        $annotation = $this->singleFlightAnnotation($class, $method);
        if ($annotation === null) {
            throw new AnnotationException("Annotation SingleFlight couldn't be collected successfully.");
        }

        if ($value = $annotation->value) {
            preg_match_all('/#\{[\w.]+}/', $value, $matches);
            $matches = $matches[0];
            if ($matches) {
                foreach ($matches as $search) {
                    $k = str_replace(['#{', '}'], '', $search);
                    $value = Str::replaceFirst($search, (string) data_get($arguments, $k), $value);
                }
            }
        } else {
            $value = implode(':', $arguments);
        }

        return $value;
    }

    /**
     * @throws SingleFlightException
     * @throws AnnotationException
     * @throws Throwable
     */
    private function shareCall(string $barrierKey, ProceedingJoinPoint $proceedingJoinPoint)
    {
        $class = $proceedingJoinPoint->className;
        $method = $proceedingJoinPoint->methodName;
        $annotation = $this->singleFlightAnnotation($class, $method);
        if ($annotation === null) {
            throw new AnnotationException("Annotation SingleFlight couldn't be collected successfully.");
        }

        return SingleFlight::do($barrierKey, static fn () => $proceedingJoinPoint->process(), $annotation->timeout);
    }
}
