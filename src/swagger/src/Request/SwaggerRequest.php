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
namespace Hyperf\Swagger\Request;

use Hyperf\Context\RequestContext;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Swagger\Exception\RuntimeException;
use Hyperf\Validation\Request\FormRequest;

class SwaggerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        /** @var Dispatched $dispatched */
        $dispatched = RequestContext::getOrNull()?->getAttribute(Dispatched::class);
        if (! $dispatched) {
            throw new RuntimeException('The request is invalid.');
        }

        $callback = $dispatched->handler?->callback;
        if (! $callback || ! is_array($callback)) {
            throw new RuntimeException('The SwaggerRequest is only used by swagger annotations.');
        }

        return RuleCollector::get($callback[0], $callback[1]);
    }

    public function attributes(): array
    {
        /** @var Dispatched $dispatched */
        $dispatched = RequestContext::getOrNull()?->getAttribute(Dispatched::class);
        if (! $dispatched) {
            throw new RuntimeException('The request is invalid.');
        }

        $callback = $dispatched->handler?->callback;
        if (! $callback || ! is_array($callback)) {
            throw new RuntimeException('The SwaggerRequest is only used by swagger annotations.');
        }

        return AttributeCollector::get($callback[0], $callback[1]);
    }
}
