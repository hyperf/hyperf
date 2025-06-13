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

namespace Hyperf\JsonRpc;

use Hyperf\Contract\NormalizerInterface;
use Hyperf\Rpc\Context;
use Hyperf\Rpc\ErrorResponse;
use Hyperf\Rpc\Request;
use Hyperf\Rpc\Response;
use Throwable;

class NormalizeDataFormatter extends DataFormatter
{
    public function __construct(private NormalizerInterface $normalizer, Context $context)
    {
        parent::__construct($context);
    }

    public function formatRequest(Request $request): array
    {
        return parent::formatRequest(
            $request->setParams(
                $this->normalizer->normalize($request->getParams())
            )
        );
    }

    public function formatResponse(Response $response): array
    {
        return parent::formatResponse(
            $response->setResult(
                $this->normalizer->normalize($response->getResult())
            )
        );
    }

    public function formatErrorResponse(ErrorResponse $response): array
    {
        $exception = $response->getException();
        if ($exception instanceof Throwable) {
            $exception = [
                'class' => get_class($exception),
                'attributes' => $this->normalizer->normalize($exception),
            ];
        }
        return parent::formatErrorResponse($response->setException($exception));
    }
}
