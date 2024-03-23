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

use HyperfTest\Resource\Stubs\Models\Subscription;

class PostResourceWithOptionalPivotRelationship extends PostResource
{
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'subscription' => $this->whenPivotLoaded(Subscription::class, function () {
                return [
                    'foo' => 'bar',
                ];
            }),
            'custom_subscription' => $this->whenPivotLoadedAs('accessor', Subscription::class, function () {
                return [
                    'foo' => 'bar',
                ];
            }),
        ];
    }
}
