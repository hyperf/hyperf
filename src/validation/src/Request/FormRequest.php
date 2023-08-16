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
namespace Hyperf\Validation\Request;

use Hyperf\Collection\Arr;
use Hyperf\Context\Context;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\HttpServer\Request;
use Hyperf\Validation\Contract\ValidatesWhenResolved;
use Hyperf\Validation\Contract\ValidatorFactoryInterface as ValidationFactory;
use Hyperf\Validation\UnauthorizedException;
use Hyperf\Validation\ValidatesWhenResolvedTrait;
use Hyperf\Validation\ValidationException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class FormRequest extends Request implements ValidatesWhenResolved
{
    use ValidatesWhenResolvedTrait;

    /**
     * The key to be used for the view error bag.
     */
    protected string $errorBag = 'default';

    /**
     * The scenes defined by developer.
     */
    protected array $scenes = [];

    /**
     * The input keys that should not be flashed on redirect.
     *
     * @var array
     */
    protected $dontFlash = ['password', 'password_confirmation'];

    public function __construct(protected ContainerInterface $container)
    {
    }

    public function scene(string $scene): static
    {
        Context::set($this->getContextValidatorKey('scene'), $scene);
        return $this;
    }

    public function getScene(): ?string
    {
        return Context::get($this->getContextValidatorKey('scene'));
    }

    /**
     * Get the proper failed validation response for the request.
     */
    public function response(): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = Context::get(ResponseInterface::class);

        return $response->withStatus(422);
    }

    /**
     * Get the validated data from the request.
     */
    public function validated(): array
    {
        return $this->getValidatorInstance()->validated();
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Set the container implementation.
     */
    public function setContainer(ContainerInterface $container): static
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the validator instance for the request.
     */
    protected function getValidatorInstance(): ValidatorInterface
    {
        return Context::getOrSet($this->getContextValidatorKey(ValidatorInterface::class), function () {
            $factory = $this->container->get(ValidationFactory::class);

            if (method_exists($this, 'validator')) {
                $validator = call_user_func_array([$this, 'validator'], compact('factory'));
            } else {
                $validator = $this->createDefaultValidator($factory);
            }

            if (method_exists($this, 'withValidator')) {
                $this->withValidator($validator);
            }

            return $validator;
        });
    }

    /**
     * Create the default validator instance.
     */
    protected function createDefaultValidator(ValidationFactory $factory): ValidatorInterface
    {
        return $factory->make(
            $this->validationData(),
            $this->getRules(),
            $this->messages(),
            $this->attributes()
        );
    }

    /**
     * Get data to be validated from the request.
     */
    protected function validationData(): array
    {
        return array_merge_recursive($this->all(), $this->getUploadedFiles());
    }

    /**
     * Handle a failed validation attempt.
     *
     * @throws ValidationException
     */
    protected function failedValidation(ValidatorInterface $validator)
    {
        throw new ValidationException($validator, $this->response());
    }

    /**
     * Format the errors from the given Validator instance.
     */
    protected function formatErrors(ValidatorInterface $validator): array
    {
        return $validator->getMessageBag()->toArray();
    }

    /**
     * Determine if the request passes the authorization check.
     */
    protected function passesAuthorization(): bool
    {
        if (method_exists($this, 'authorize')) {
            return call_user_func_array([$this, 'authorize'], []);
        }

        return false;
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        throw new UnauthorizedException('This action is unauthorized.');
    }

    /**
     * Get context validator key.
     */
    protected function getContextValidatorKey(string $key): string
    {
        return sprintf('%s:%s', spl_object_hash($this), $key);
    }

    /**
     * Get scene rules.
     */
    protected function getRules(): array
    {
        $rules = call_user_func_array([$this, 'rules'], []);
        $scene = $this->getScene();
        if ($scene && isset($this->scenes[$scene]) && is_array($this->scenes[$scene])) {
            return Arr::only($rules, $this->scenes[$scene]);
        }
        return $rules;
    }
}
