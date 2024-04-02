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

namespace HyperfTest\Resource\Stubs\Resources;

use Hyperf\Resource\Json\JsonResource;

class PostResourceWithOptionalData extends JsonResource
{
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'first' => $this->when(false, 'value'),
            'second' => $this->when(true, 'value'),
            'third' => $this->when(true, function () {
                return 'value';
            }),
            'fourth' => $this->when(false, 'value', 'default'),
            'fifth' => $this->when(false, 'value', function () {
                return 'default';
            }),
        ];
    }
}
