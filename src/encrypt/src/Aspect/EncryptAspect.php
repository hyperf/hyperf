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

namespace Hyperf\Encrypt\Aspect;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Encrypt\Annotation\Encrypt;
use Hyperf\Encrypt\Handler\EncryptHandlerInterface;
use Hyperf\Encrypt\SecretKeyInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @Aspect
 */
class EncryptAspect extends AbstractAspect
{
    public $annotations = [
        Encrypt::class,
    ];

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var RequestInterface
     */
    protected $request;

    public function __construct(ConfigInterface $config, RequestInterface $request)
    {
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * @return mixed return the value from process method of ProceedingJoinPoint, or the value that you handled
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $annotation = $this->getAnnotationMetadata($proceedingJoinPoint);

        $secretKey = make(SecretKeyInterface::class, get_object_vars($annotation));
        $encryptHandler = make(EncryptHandlerInterface::class, ['secretKey' => $secretKey]);

        if ($annotation->before && method_exists($encryptHandler, $annotation->before)) {
            $parsedBody = $encryptHandler->{$annotation->before}($this->request->getBody()->getContents());
            if ($parsedBody) {
                Context::set('http.request.parsedData', null);
                Context::set(ServerRequestInterface::class, $this->request->withParsedBody($parsedBody));
            }
        }

        $result = $proceedingJoinPoint->process();

        if ($annotation->after && method_exists($encryptHandler, $annotation->after)) {
            $result = $encryptHandler->{$annotation->after}($result);
        }
        return $result;
    }

    private function getAnnotationMetadata(ProceedingJoinPoint $proceedingJoinPoint): Encrypt
    {
        /** @var Encrypt $propertyAnnotation */
        $propertyAnnotation = $proceedingJoinPoint->getAnnotationMetadata()->method[Encrypt::class] ?? null;

        /** @var Encrypt $classAnnotation */
        $classAnnotation = $proceedingJoinPoint->getAnnotationMetadata()->class[Encrypt::class] ?? null;

        $configAnnotation = $this->config->get('encrypt', []);

        return new class($propertyAnnotation, $classAnnotation, $configAnnotation) extends Encrypt {
            public function __construct(?Encrypt $propertyAnnotation, ?Encrypt $classAnnotation, array $configAnnotation)
            {
                foreach (get_object_vars($this) as $property => $var) {
                    $this->{$property} = $propertyAnnotation->{$property} ?? $classAnnotation->{$property} ?? $configAnnotation[$property] ?? $var;
                }
            }
        };
    }
}
