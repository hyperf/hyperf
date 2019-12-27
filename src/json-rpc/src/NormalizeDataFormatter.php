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

namespace Hyperf\JsonRpc;

use Hyperf\Contract\NormalizerInterface;

class NormalizeDataFormatter extends DataFormatter
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function formatRequest($data)
    {
        $data[1] = $this->normalizer->normalize($data[1]);
        return parent::formatRequest($data);
    }

    public function formatResponse($data)
    {
        $data[1] = $this->normalizer->normalize($data[1]);
        return parent::formatResponse($data);
    }

    public function formatErrorResponse($data)
    {
        if (isset($data[3]) && $data[3] instanceof \Exception) {
            $data[3] = [
                'class' => get_class($data[3]),
                'attributes' => $this->normalizer->normalize($data[3]),
            ];
        }
        return parent::formatErrorResponse($data);
    }
}
