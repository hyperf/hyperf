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
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\AnnotationException;
use Hyperf\SingleFlight\Annotation\SingleFlight;
use Hyperf\SingleFlight\Barrier;
use Hyperf\Stringable\Str;

use function Hyperf\Collection\data_get;

class SingleFlightAspect extends AbstractAspect
{
    public array $annotations = [
        SingleFlight::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->shouldHijacked($proceedingJoinPoint)) {
            return $proceedingJoinPoint->process();
        }

        $barrierKey = $this->barrierKey($proceedingJoinPoint);

        return $this->shareCall($barrierKey, $proceedingJoinPoint);
    }

    private function shouldHijacked(ProceedingJoinPoint $proceedingJoinPoint): bool
    {
        if (! Coroutine::inCoroutine()) {
            return false;
        }

        $annotation = $this->methodAnnotation($proceedingJoinPoint->className, $proceedingJoinPoint->methodName, SingleFlight::class);

        return (bool) $annotation?->value;
    }

    private function methodAnnotation(string $class, string $method, string $annotation): ?AbstractAnnotation
    {
        return AnnotationCollector::getClassMethodAnnotation($class, $method)[$annotation] ?? null;
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
        $annotation = $this->methodAnnotation($class, $method, SingleFlight::class);
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

    private function shareCall(string $barrierKey, ProceedingJoinPoint $proceedingJoinPoint)
    {
        return Barrier::yield($barrierKey, static fn () => $proceedingJoinPoint->process());
    }
}
