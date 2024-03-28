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
        $callback = $this->getCallbackByContext();

        return ValidationCollector::get($callback[0], $callback[1], 'rules');
    }

    public function attributes(): array
    {
        $callback = $this->getCallbackByContext();

        return ValidationCollector::get($callback[0], $callback[1], 'attribute');
    }

    protected function getCallbackByContext(): array
    {
        /** @var null|Dispatched $dispatched */
        $dispatched = RequestContext::getOrNull()?->getAttribute(Dispatched::class);
        if (! $dispatched) {
            throw new RuntimeException('The request is invalid.');
        }

        $callback = $dispatched->handler?->callback;
        if (! $callback) {
            throw new RuntimeException('The SwaggerRequest is only used by swagger annotations.');
        }

        return $this->prepareHandler($callback);
    }

    /**
     * @see \Hyperf\HttpServer\CoreMiddleware::prepareHandler()
     */
    protected function prepareHandler(array|string $handler): array
    {
        if (is_string($handler)) {
            if (str_contains($handler, '@')) {
                return explode('@', $handler);
            }
            $array = explode('::', $handler);
            if (! isset($array[1]) && class_exists($handler) && method_exists($handler, '__invoke')) {
                $array[1] = '__invoke';
            }
            return [$array[0], $array[1] ?? null];
        }
        if (is_array($handler) && isset($handler[0], $handler[1])) {
            return $handler;
        }
        throw new RuntimeException('Handler not exist.');
    }
}
