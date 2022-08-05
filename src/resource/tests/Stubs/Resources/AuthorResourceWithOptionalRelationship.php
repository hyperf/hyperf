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

class AuthorResourceWithOptionalRelationship extends PostResource
{
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'posts_count' => $this->whenLoaded('posts', fn() => $this->posts->count() . ' posts', fn() => 'not loaded'),
            'latest_post_title' => $this->whenLoaded('posts', fn() => optional($this->posts->first())->title ?: 'no posts yet', 'not loaded'),
        ];
    }
}
