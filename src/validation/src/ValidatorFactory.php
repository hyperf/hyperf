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

use Closure;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Stringable\StrCache;
use Hyperf\Validation\Contract\PresenceVerifierInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Psr\Container\ContainerInterface;

class ValidatorFactory implements ValidatorFactoryInterface
{
    /**
     * The Presence Verifier implementation.
     *
     * @var PresenceVerifierInterface
     */
    protected $verifier;

    /**
     * All the custom validator extensions.
     *
     * @var array
     */
    protected $extensions = [];

    /**
     * All the custom implicit validator extensions.
     *
     * @var array
     */
    protected $implicitExtensions = [];

    /**
     * All the custom dependent validator extensions.
     *
     * @var array
     */
    protected $dependentExtensions = [];

    /**
     * All the custom validator message replacers.
     *
     * @var array
     */
    protected $replacers = [];

    /**
     * All the fallback messages for custom rules.
     *
     * @var array
     */
    protected $fallbackMessages = [];

    /**
     * The Validator resolver instance.
     *
     * @var Closure
     */
    protected $resolver;

    /**
     * Create a new Validator factory instance.
     */
    public function __construct(
        protected TranslatorInterface $translator,
        protected ?ContainerInterface $container = null
    ) {
    }

    /**
     * Create a new Validator instance.
     */
    public function make(array $data, array $rules, array $messages = [], array $customAttributes = []): ValidatorInterface
    {
        $validator = $this->resolve(
            $data,
            $rules,
            $messages,
            $customAttributes
        );

        // The presence verifier is responsible for checking the unique and exists data
        // for the validator. It is behind an interface so that multiple versions of
        // it may be written besides database. We'll inject it into the validator.
        if (! is_null($this->verifier)) {
            $validator->setPresenceVerifier($this->verifier);
        }

        // Next we'll set the IoC container instance of the validator, which is used to
        // resolve out class based validator extensions. If it is not set then these
        // types of extensions will not be possible on these validation instances.
        if (! is_null($this->container)) {
            $validator->setContainer($this->container);
        }

        $validator instanceof Validator && $this->addExtensions($validator);

        return $validator;
    }

    /**
     * Validate the given data against the provided rules.
     *
     * @throws ValidationException
     */
    public function validate(array $data, array $rules, array $messages = [], array $customAttributes = []): array
    {
        return $this->make($data, $rules, $messages, $customAttributes)->validate();
    }

    /**
     * Register a custom validator extension.
     */
    public function extend(string $rule, Closure|string $extension, ?string $message = null)
    {
        $this->extensions[$rule] = $extension;

        if ($message) {
            $this->fallbackMessages[StrCache::snake($rule)] = $message;
        }
    }

    /**
     * Register a custom implicit validator extension.
     */
    public function extendImplicit(string $rule, Closure|string $extension, ?string $message = null)
    {
        $this->implicitExtensions[$rule] = $extension;

        if ($message) {
            $this->fallbackMessages[StrCache::snake($rule)] = $message;
        }
    }

    /**
     * Register a custom dependent validator extension.
     */
    public function extendDependent(string $rule, Closure|string $extension, ?string $message = null)
    {
        $this->dependentExtensions[$rule] = $extension;

        if ($message) {
            $this->fallbackMessages[StrCache::snake($rule)] = $message;
        }
    }

    /**
     * Register a custom validator message replacer.
     */
    public function replacer(string $rule, Closure|string $replacer)
    {
        $this->replacers[$rule] = $replacer;
    }

    /**
     * Set the Validator instance resolver.
     */
    public function resolver(Closure $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Get the Translator implementation.
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * Get the Presence Verifier implementation.
     */
    public function getPresenceVerifier(): PresenceVerifierInterface
    {
        return $this->verifier;
    }

    /**
     * Set the Presence Verifier implementation.
     */
    public function setPresenceVerifier(PresenceVerifierInterface $presenceVerifier)
    {
        $this->verifier = $presenceVerifier;
    }

    /**
     * Resolve a new Validator instance.
     */
    protected function resolve(array $data, array $rules, array $messages, array $customAttributes): ValidatorInterface
    {
        if (is_null($this->resolver)) {
            return new Validator($this->translator, $data, $rules, $messages, $customAttributes);
        }

        return call_user_func($this->resolver, $this->translator, $data, $rules, $messages, $customAttributes);
    }

    /**
     * Add the extensions to a validator instance.
     */
    protected function addExtensions(Validator $validator): void
    {
        $validator->addExtensions($this->extensions);

        // Next, we will add the implicit extensions, which are similar to the required
        // and accepted rule in that they are run even if the attributes is not in a
        // array of data that is given to a validator instances via instantiation.
        $validator->addImplicitExtensions($this->implicitExtensions);

        $validator->addDependentExtensions($this->dependentExtensions);

        $validator->addReplacers($this->replacers);

        $validator->setFallbackMessages($this->fallbackMessages);
    }
}
