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

use Hyperf\Resource\Json\ResourceCollection;

class PostCollectionResource extends ResourceCollection
{
    public ?string $collects = PostResource::class;

    public function toArray(): array
    {
        return ['data' => $this->collection];
    }
}
