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

class BarSceneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $scene = $this->getScene();

        switch ($scene) {
            case 'required':
                return [
                    'name' => 'required',
                ];
            default:
                return [
                    'name' => 'required|integer',
                ];
        }
    }
}
