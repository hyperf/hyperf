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

namespace HyperfTest\Validation\Cases\Stub;

use Hyperf\Validation\Request\FormRequest;

class FooSceneRequest extends FormRequest
{
    public array $scenes = [
        'save' => ['mobile', 'name'],
        'info' => ['mobile'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mobile' => 'required',
            'name' => 'required',
        ];
    }
}
