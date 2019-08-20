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

namespace Hyperf\Validation;

/**
 * Provides default implementation of ValidatesWhenResolved contract.
 */
trait ValidatesWhenResolvedTrait
{
    /**
     * Validate the class instance.
     */
    public function validateResolved()
    {
        $this->prepareForValidation();

        if (! $this->passesAuthorization()) {
            $this->failedAuthorization();
        }

        $instance = $this->getValidatorInstance();

        if ($instance->fails()) {
            $this->failedValidation($instance);
        }
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // no default action
    }

    /**
     * Get the validator instance for the request.
     *
     * @return \Hyperf\Validation\Contracts\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        return $this->validator();
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param \Hyperf\Validation\Contracts\Validation\Validator $validator
     *
     * @throws \Hyperf\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);
    }

    /**
     * Determine if the request passes the authorization check.
     *
     * @return bool
     */
    protected function passesAuthorization():bool
    {
        if (method_exists($this, 'authorize')) {
            return $this->authorize();
        }

        return true;
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @throws \Hyperf\Validation\UnauthorizedException
     */
    protected function failedAuthorization()
    {
        throw new UnauthorizedException();
    }
}
