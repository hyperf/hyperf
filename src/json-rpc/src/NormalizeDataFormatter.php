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

class NormalizeDataFormatter extends DataFormatter
{
    public function __construct(private NormalizerInterface $normalizer, Context $context)
    {
        parent::__construct($context);
    }

    public function formatRequest(array $data): array
    {
        $data[1] = $this->normalizer->normalize($data[1]);
        return parent::formatRequest($data);
    }

    public function formatResponse(array $data): array
    {
        $data[1] = $this->normalizer->normalize($data[1]);
        return parent::formatResponse($data);
    }

    public function formatErrorResponse(array $data): array
    {
        if (isset($data[3]) && $data[3] instanceof \Throwable) {
            $data[3] = [
                'class' => get_class($data[3]),
                'attributes' => $this->normalizer->normalize($data[3]),
            ];
        }
        return parent::formatErrorResponse($data);
    }
}
