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

use Hyperf\HttpServer\Request;
use Hyperf\Validation\Contracts\Validation\Factory;
use Hyperf\Validation\Contracts\Validation\Factory as ValidationFactory;
use Hyperf\Validation\Contracts\Validation\ValidatesWhenResolved;
use Hyperf\Validation\Contracts\Validation\Validator;
use Hyperf\Validation\ValidatesWhenResolvedTrait;
use Hyperf\Validation\ValidationException;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class FormRequest extends Request implements ValidatesWhenResolved
{
    use ValidatesWhenResolvedTrait;

    /**
     * The container instance.
     *
     * @var ContainerInterface
     */
    protected $container;

//    /**
//     * The redirector instance.
//     *
//     * @var \Illuminate\Routing\Redirector
//     */
//    protected $redirector;
//
//    /**
//     * The URI to redirect to if validation fails.
//     *
//     * @var string
//     */
//    protected $redirect;
//
//    /**
//     * The route to redirect to if validation fails.
//     *
//     * @var string
//     */
//    protected $redirectRoute;
//
//    /**
//     * The controller action to redirect to if validation fails.
//     *
//     * @var string
//     */
//    protected $redirectAction;

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
     * @param array $errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function response(array $errors)
    {
//        if ($this->expectsJson()) {
//            return new JsonResponse($errors, 422);
//        }

//        return $this->redirector->to($this->getRedirectUrl())
//            ->withInput($this->except($this->dontFlash))
//            ->withErrors($errors, $this->errorBag);
        return new JsonResponse($errors, 422);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [];
    }

//    /**
//     * Set the Redirector instance.
//     *
//     * @param  \Illuminate\Routing\Redirector $redirector
//     * @return $this
//     */
//    public function setRedirector(Redirector $redirector)
//    {
//        $this->redirector = $redirector;
//
//        return $this;
//    }

    /**
     * Set the container implementation.
     *
     * @param ContainerInterface $container
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
     * @return \Hyperf\Validation\Contracts\Validation\Validator
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
     * @param Factory $factory
     * @return \Hyperf\Validation\Contracts\Validation\Validator
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
     * @param Validator $validator
     *
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, $this->response(
            $this->formatErrors($validator)
        ));
    }

    /**
     * Format the errors from the given Validator instance.
     *
     * @param Validator $validator
     * @return array
     */
    protected function formatErrors(Validator $validator)
    {
        return $validator->getMessageBag()->toArray();
    }

//    /**
//     * Get the URL to redirect to on a validation error.
//     *
//     * @return string
//     */
//    protected function getRedirectUrl()
//    {
//        $url = $this->redirector->getUrlGenerator();
//
//        if ($this->redirect) {
//            return $url->to($this->redirect);
//        } elseif ($this->redirectRoute) {
//            return $url->route($this->redirectRoute);
//        } elseif ($this->redirectAction) {
//            return $url->action($this->redirectAction);
//        }
//
//        return $url->previous();
//    }

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
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function failedAuthorization()
    {
        // throw new AuthorizationException('This action is unauthorized.');
    }
}
