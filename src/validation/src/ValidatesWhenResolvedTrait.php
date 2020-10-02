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
namespace Hyperf\Validation;

use Hyperf\Contract\ValidatorInterface;

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
     */
    protected function getValidatorInstance(): ValidatorInterface
    {
        return $this->validator();
    }

    /**
     * Handle a failed validation attempt.
     *
     * @throws \Hyperf\Validation\ValidationException
     */
    protected function failedValidation(ValidatorInterface $validator)
    {
        throw new ValidationException($validator);
    }

    /**
     * Determine if the request passes the authorization check.
     */
    protected function passesAuthorization(): bool
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
