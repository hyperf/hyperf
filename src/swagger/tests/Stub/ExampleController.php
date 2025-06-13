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

namespace HyperfTest\Swagger\Stub;

use Hyperf\Swagger\Annotation as SA;
use Hyperf\Swagger\Request\SwaggerRequest;

class ExampleController
{
    #[SA\Post('/hyperf/example/index', summary: '单测 index', tags: ['hyperf'])]
    #[SA\QueryParameter(
        name: 'token',
        description: 'token',
        example: 'token',
        rules: 'required|string|max:25',
    )]
    #[SA\RequestBody(
        description: '请求参数',
        content: [
            new SA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new SA\Schema(
                    properties: [
                        new SA\Property(property: 'name', description: '昵称', type: 'string', example: 'user-2', rules: 'required|string|max:3', attribute: 'nickname'),
                    ]
                ),
            ),
        ],
    )]
    public function index(SwaggerRequest $request): void
    {
    }

    #[SA\Post('/hyperf/example/json', summary: '单测 json', tags: ['hyperf'])]
    #[SA\QueryParameter(
        name: 'token',
        description: 'token',
        example: 'token',
        rules: 'required|string|max:25',
    )]
    #[SA\RequestBody(
        description: '请求参数',
        content: new SA\JsonContent(
            properties: [
                new SA\Property(property: 'name', description: '昵称', type: 'string', example: 'user-2', rules: 'required|int', attribute: 'json-name'),
            ]
        ),
    )]
    public function json(SwaggerRequest $request): void
    {
    }
}
