<?php
/**
 * FormRequest.php
 *
 * Author: wangyi <chunhei2008@qq.com>
 *
 * Date:   2019/7/26 02:04
 * Copyright: (C) 2014, Guangzhou YIDEJIA Network Technology Co., Ltd.
 */

namespace Hyperf\Validation\Request;

use Hyperf\Validation\Contracts\Validation\Factory;
use Hyperf\HttpServer\Request;
//use Illuminate\Http\JsonResponse;
//use Illuminate\Routing\Redirector;
use Symfony\Component\HttpFoundation\JsonResponse;
use Hyperf\Validation\Contracts\Validation\Validator;
use Hyperf\Validation\ValidationException;
//use Illuminate\Auth\Access\AuthorizationException;
use Hyperf\Validation\Contracts\Validation\Factory as ValidationFactory;
use Hyperf\Validation\Contracts\Validation\ValidatesWhenResolved;
use Hyperf\Validation\ValidatesWhenResolvedTrait;
use Psr\Container\ContainerInterface;


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
     * @param  Factory $factory
     * @return \Hyperf\Validation\Contracts\Validation\Validator
     */
    protected function createDefaultValidator(ValidationFactory $factory)
    {
        return $factory->make(
            $this->validationData(), call_user_func_array([$this, 'rules'], []),
            $this->messages(), $this->attributes()
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
     * @param  Validator $validator
     * @return void
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
     * Get the proper failed validation response for the request.
     *
     * @param  array $errors
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
     * Format the errors from the given Validator instance.
     *
     * @param  Validator $validator
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
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function failedAuthorization()
    {
        // throw new AuthorizationException('This action is unauthorized.');
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
     * @param  ContainerInterface $container
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }
}