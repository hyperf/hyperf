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

use Hyperf\Collection\Arr;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Server\Exception\ServerException;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Psr\Http\Message\ResponseInterface;

use function Hyperf\Tappable\tap;

class ValidationException extends ServerException
{
    /**
     * The status code to use for the response.
     */
    public int $status = 422;

    /**
     * The path the client should be redirected to.
     */
    public ?string $redirectTo = null;

    /**
     * Create a new exception instance.
     *
     * @param ValidatorInterface $validator the validator instance
     * @param null|ResponseInterface $response the recommended response to send to the client
     * @param string $errorBag the name of the error bag
     */
    public function __construct(
        public ValidatorInterface $validator,
        public ?ResponseInterface $response = null,
        public string $errorBag = 'default'
    ) {
        parent::__construct('The given data was invalid.');
    }

    /**
     * Create a new validation exception from a plain array of messages.
     *
     * @return static
     */
    public static function withMessages(array $messages)
    {
        $factory = ApplicationContext::getContainer()->get(ValidatorFactoryInterface::class);

        return new static(tap($factory->make([], []), function ($validator) use ($messages) {
            foreach ($messages as $key => $value) {
                foreach (Arr::wrap($value) as $message) {
                    $validator->errors()->add($key, $message);
                }
            }
        }));
    }

    /**
     * Get all of the validation error messages.
     */
    public function errors(): array
    {
        return $this->validator->errors()->messages();
    }

    /**
     * Set the HTTP status code to be used for the response.
     *
     * @return $this
     */
    public function status(int $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Set the error bag on the exception.
     *
     * @return $this
     */
    public function errorBag(string $errorBag)
    {
        $this->errorBag = $errorBag;

        return $this;
    }

    /**
     * Set the URL to redirect to on a validation error.
     *
     * @return $this
     */
    public function redirectTo(string $url)
    {
        $this->redirectTo = $url;

        return $this;
    }

    /**
     * Get the underlying response instance.
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
