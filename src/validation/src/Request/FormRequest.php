<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Validation\Request;

use Hyperf\Contract\ValidatorInterface;
use Hyperf\HttpServer\Request;
use Hyperf\Utils\Context;
use Hyperf\Validation\Contracts\Validation\Factory as ValidationFactory;
use Hyperf\Validation\Contracts\Validation\ValidatesWhenResolved;
use Hyperf\Validation\ValidatesWhenResolvedTrait;
use Hyperf\Validation\ValidationException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class FormRequest extends Request implements ValidatesWhenResolved
{
    use ValidatesWhenResolvedTrait;

    /**
     * The container instance.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * The key to be used for the view error bag.
     *
     * @var string
     */
    protected $errorBag = 'default';

    /**
     * The input keys that should not be flashed on redirect.
     *
     * @var array
     */
    protected $dontFlash = ['password', 'password_confirmation'];

    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    /**
     * Get the proper failed validation response for the request.
     *
     * @return ResponseInterface
     */
    public function response()
    {
        /** @var ResponseInterface $response */
        $response = Context::get(ResponseInterface::class);

        return $response->withStatus(422);
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
     *
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the validator instance for the request.
     *
     * @return ValidatorInterface
     */
    protected function getValidatorInstance()
    {
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
    }

    /**
     * Create the default validator instance.
     *
     * @return ValidatorInterface
     */
    protected function createDefaultValidator(ValidationFactory $factory)
    {
        return $factory->make(
            $this->validationData(),
            call_user_func_array([$this, 'rules'], []),
            $this->messages(),
            $this->attributes()
        );
    }

    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    protected function validationData()
    {
        return $this->all();
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
     *
     * @return bool
     */
    protected function passesAuthorization()
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
        // throw new AuthorizationException('This action is unauthorized.');
    }
}
