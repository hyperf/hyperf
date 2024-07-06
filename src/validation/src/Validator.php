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

use BadMethodCallException;
use Closure;
use Hyperf\Collection\Arr;
use Hyperf\Contract\MessageBag as MessageBagContract;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Contract\ValidatorInterface as ValidatorContract;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\Stringable\Str;
use Hyperf\Stringable\StrCache;
use Hyperf\Support\Fluent;
use Hyperf\Support\MessageBag;
use Hyperf\Validation\Contract\DataAwareRule;
use Hyperf\Validation\Contract\ImplicitRule;
use Hyperf\Validation\Contract\PresenceVerifierInterface;
use Hyperf\Validation\Contract\Rule as RuleContract;
use Hyperf\Validation\Contract\ValidatorAwareRule;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Stringable;

use function Hyperf\Collection\collect;
use function Hyperf\Collection\data_get;
use function Hyperf\Support\make;
use function Hyperf\Tappable\tap;

class Validator implements ValidatorContract
{
    use Concerns\FormatsMessages;
    use Concerns\ValidatesAttributes;

    /**
     * The array of fallback error messages.
     */
    public array $fallbackMessages = [];

    /**
     * The array of custom displayable values.
     */
    public array $customValues = [];

    /**
     * All the custom validator extensions.
     */
    public array $extensions = [];

    /**
     * All the custom replacer extensions.
     */
    public array $replacers = [];

    /**
     * The container instance.
     */
    protected ?ContainerInterface $container = null;

    /**
     * The Presence Verifier implementation.
     */
    protected ?PresenceVerifierInterface $presenceVerifier = null;

    /**
     * The failed validation rules.
     */
    protected array $failedRules = [];

    /**
     * The message bag instance.
     */
    protected ?MessageBag $messages = null;

    /**
     * The data under validation.
     */
    protected array $data;

    /**
     * The rules to be applied to the data.
     */
    protected array $rules = [];

    /**
     * The current rule that is validating.
     *
     * @var string|Stringable
     */
    protected mixed $currentRule;

    /**
     * The array of wildcard attributes with their asterisks expanded.
     */
    protected array $implicitAttributes = [];

    /**
     * The cached data for the "distinct" rule.
     */
    protected array $distinctValues = [];

    /**
     * All the registered "after" callbacks.
     */
    protected array $after = [];

    /**
     * The validation rules that may be applied to files.
     */
    protected array $fileRules = [
        'File', 'Image', 'Mimes', 'Mimetypes', 'Min',
        'Max', 'Size', 'Between', 'Dimensions',
    ];

    /**
     * The validation rules that imply the field is required.
     */
    protected array $implicitRules = [
        'Required', 'Filled', 'RequiredWith', 'RequiredWithAll', 'RequiredWithout',
        'RequiredWithoutAll', 'RequiredIf', 'RequiredUnless', 'Accepted', 'Present',
    ];

    /**
     * The validation rules which depend on other fields as parameters.
     */
    protected array $dependentRules = [
        'RequiredWith', 'RequiredWithAll', 'RequiredWithout', 'RequiredWithoutAll',
        'RequiredIf', 'RequiredUnless', 'Confirmed', 'Same', 'Different', 'Unique',
        'Before', 'After', 'BeforeOrEqual', 'AfterOrEqual', 'Gt', 'Lt', 'Gte', 'Lte',
        'Prohibits',
    ];

    /**
     * The size related validation rules.
     */
    protected array $sizeRules = ['Size', 'Between', 'Min', 'Max', 'Gt', 'Lt', 'Gte', 'Lte'];

    /**
     * The numeric related validation rules.
     */
    protected array $numericRules = ['Numeric', 'Integer', 'Decimal'];

    /**
     * @param TranslatorInterface $translator the Translator implementation
     * @param array $initialRules the initial rules provided
     * @param array $customMessages the array of custom error messages
     * @param array $customAttributes the array of custom attribute names
     */
    public function __construct(
        protected TranslatorInterface $translator,
        array $data,
        protected array $initialRules,
        public array $customMessages = [],
        public array $customAttributes = []
    ) {
        $this->data = $this->parseData($data);

        $this->setRules($initialRules);
    }

    /**
     * Handle dynamic calls to class methods.
     *
     * @param mixed $method
     * @param mixed $parameters
     * @throws BadMethodCallException when method does not exist
     */
    public function __call($method, $parameters)
    {
        $rule = StrCache::snake(substr($method, 8));

        if (isset($this->extensions[$rule])) {
            return $this->callExtension($rule, $parameters);
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.',
            static::class,
            $method
        ));
    }

    /**
     * Parse the data array, converting dots to ->.
     */
    public function parseData(array $data): array
    {
        $newData = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->parseData($value);
            }

            // If the data key contains a dot, we will replace it with another character
            // sequence so it doesn't interfere with dot processing when working with
            // array based validation rules and array_dot later in the validations.
            if (Str::contains((string) $key, '.')) {
                $newData[str_replace('.', '->', $key)] = $value;
            } else {
                $newData[$key] = $value;
            }
        }

        return $newData;
    }

    /**
     * Add an after validation callback.
     *
     * @param callable|string $callback
     */
    public function after($callback): self
    {
        $this->after[] = fn () => call_user_func_array($callback, [$this]);

        return $this;
    }

    /**
     * Determine if the data passes the validation rules.
     */
    public function passes(): bool
    {
        $this->messages = new MessageBag();

        [$this->distinctValues, $this->failedRules] = [[], []];

        // We'll spin through each rule, validating the attributes attached to that
        // rule. Any error messages will be added to the containers with each of
        // the other error messages, returning true if we don't have messages.
        foreach ($this->rules as $attribute => $rules) {
            $attribute = str_replace('\.', '->', $attribute);

            foreach ($rules as $rule) {
                $this->validateAttribute($attribute, $rule);

                if ($this->shouldStopValidating($attribute)) {
                    break;
                }
            }
        }

        // Here we will spin through all of the "after" hooks on this validator and
        // fire them off. This gives the callbacks a chance to perform all kinds
        // of other validation that needs to get wrapped up in this operation.
        foreach ($this->after as $after) {
            call_user_func($after);
        }

        return $this->messages->isEmpty();
    }

    /**
     * Determine if the data fails the validation rules.
     */
    public function fails(): bool
    {
        return ! $this->passes();
    }

    /**
     * Run the validator's rules against its data.
     *
     * @throws ValidationException if validate fails
     */
    public function validate(): array
    {
        if ($this->fails()) {
            throw new ValidationException($this);
        }

        return $this->validated();
    }

    /**
     * Get the attributes and values that were validated.
     *
     * @throws ValidationException if invalid
     */
    public function validated(): array
    {
        if ($this->invalid()) {
            throw new ValidationException($this);
        }

        $results = [];

        $missingValue = Str::random(10);

        foreach (array_keys($this->getRules()) as $key) {
            $value = data_get($this->getData(), $key, $missingValue);

            if ($value !== $missingValue) {
                Arr::set($results, $key, $value);
            }
        }

        return $results;
    }

    /**
     * Add a failed rule and error message to the collection.
     */
    public function addFailure(string $attribute, string $rule, array $parameters = [])
    {
        if (! $this->messages) {
            $this->passes();
        }

        $this->messages->add($attribute, $this->makeReplacements(
            $this->getMessage($attribute, $rule),
            $attribute,
            $rule,
            $parameters
        ));

        $this->failedRules[$attribute][$rule] = $parameters;
    }

    /**
     * Returns the data which was valid.
     */
    public function valid(): array
    {
        if (! $this->messages) {
            $this->passes();
        }

        return array_diff_key(
            $this->data,
            $this->attributesThatHaveMessages()
        );
    }

    /**
     * Returns the data which was invalid.
     */
    public function invalid(): array
    {
        if (! $this->messages) {
            $this->passes();
        }

        return array_intersect_key(
            $this->data,
            $this->attributesThatHaveMessages()
        );
    }

    /**
     * Get the failed validation rules.
     */
    public function failed(): array
    {
        return $this->failedRules;
    }

    /**
     * Get the message container for the validator.
     *
     * @return MessageBag
     */
    public function messages()
    {
        if (! $this->messages) {
            $this->passes();
        }

        return $this->messages;
    }

    /**
     * An alternative more semantic shortcut to the message container.
     */
    public function errors(): MessageBagContract
    {
        return $this->messages();
    }

    /**
     * Get the messages for the instance.
     */
    public function getMessageBag(): MessageBagContract
    {
        return $this->messages();
    }

    /**
     * Determine if the given attribute has a rule in the given set.
     *
     * @param array|string|Stringable $rules
     */
    public function hasRule(string $attribute, mixed $rules): bool
    {
        return ! is_null($this->getRule($attribute, $rules));
    }

    /**
     * Get the data under validation.
     */
    public function attributes(): array
    {
        return $this->getData();
    }

    /**
     * Get the data under validation.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set the data under validation.
     */
    public function setData(array $data): self
    {
        $this->data = $this->parseData($data);

        $this->setRules($this->initialRules);

        return $this;
    }

    /**
     * Get the validation rules.
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Set the validation rules.
     */
    public function setRules(array $rules): self
    {
        $this->initialRules = $rules;

        $this->rules = [];

        $this->addRules($rules);

        return $this;
    }

    /**
     * Parse the given rules and merge them into current rules.
     */
    public function addRules(array $rules)
    {
        // The primary purpose of this parser is to expand any "*" rules to the all
        // of the explicit rules needed for the given data. For example the rule
        // names.* would get expanded to names.0, names.1, etc. for this data.
        $response = (new ValidationRuleParser($this->data))
            ->explode($rules);

        $this->rules = array_merge_recursive(
            $this->rules,
            $response->rules
        );

        $this->implicitAttributes = array_merge(
            $this->implicitAttributes,
            $response->implicitAttributes
        );
    }

    /**
     * Add conditions to a given field based on a Closure.
     *
     * @param array|string $attribute
     * @param array|string $rules
     */
    public function sometimes($attribute, $rules, callable $callback): self
    {
        $payload = new Fluent($this->getData());

        if (call_user_func($callback, $payload)) {
            foreach ((array) $attribute as $key) {
                $this->addRules([$key => $rules]);
            }
        }

        return $this;
    }

    /**
     * Register an array of custom validator extensions.
     */
    public function addExtensions(array $extensions)
    {
        if ($extensions) {
            $keys = array_map('\Hyperf\Stringable\StrCache::snake', array_keys($extensions));

            $extensions = array_combine($keys, array_values($extensions));
        }

        $this->extensions = array_merge($this->extensions, $extensions);
    }

    /**
     * Register an array of custom implicit validator extensions.
     */
    public function addImplicitExtensions(array $extensions)
    {
        $this->addExtensions($extensions);

        foreach ($extensions as $rule => $extension) {
            $this->implicitRules[] = StrCache::studly($rule);
        }
    }

    /**
     * Register an array of custom implicit validator extensions.
     */
    public function addDependentExtensions(array $extensions)
    {
        $this->addExtensions($extensions);

        foreach ($extensions as $rule => $extension) {
            $this->dependentRules[] = StrCache::studly($rule);
        }
    }

    /**
     * Register a custom validator extension.
     */
    public function addExtension(string $rule, Closure|string $extension)
    {
        $this->extensions[StrCache::snake($rule)] = $extension;
    }

    /**
     * Register a custom implicit validator extension.
     */
    public function addImplicitExtension(string $rule, Closure|string $extension)
    {
        $this->addExtension($rule, $extension);

        $this->implicitRules[] = StrCache::studly($rule);
    }

    /**
     * Register a custom dependent validator extension.
     */
    public function addDependentExtension(string $rule, Closure|string $extension)
    {
        $this->addExtension($rule, $extension);

        $this->dependentRules[] = StrCache::studly($rule);
    }

    /**
     * Register an array of custom validator message replacers.
     */
    public function addReplacers(array $replacers)
    {
        if ($replacers) {
            $keys = array_map('\Hyperf\Stringable\StrCache::snake', array_keys($replacers));

            $replacers = array_combine($keys, array_values($replacers));
        }

        $this->replacers = array_merge($this->replacers, $replacers);
    }

    /**
     * Register a custom validator message replacer.
     */
    public function addReplacer(string $rule, Closure|string $replacer)
    {
        $this->replacers[StrCache::snake($rule)] = $replacer;
    }

    /**
     * Set the custom messages for the validator.
     */
    public function setCustomMessages(array $messages): self
    {
        $this->customMessages = array_merge($this->customMessages, $messages);

        return $this;
    }

    /**
     * Set the custom attributes on the validator.
     */
    public function setAttributeNames(array $attributes): self
    {
        $this->customAttributes = $attributes;

        return $this;
    }

    /**
     * Add custom attributes to the validator.
     */
    public function addCustomAttributes(array $customAttributes): self
    {
        $this->customAttributes = array_merge($this->customAttributes, $customAttributes);

        return $this;
    }

    /**
     * Set the custom values on the validator.
     */
    public function setValueNames(array $values): self
    {
        $this->customValues = $values;

        return $this;
    }

    /**
     * Add the custom values for the validator.
     */
    public function addCustomValues(array $customValues): self
    {
        $this->customValues = array_merge($this->customValues, $customValues);

        return $this;
    }

    /**
     * Set the fallback messages for the validator.
     */
    public function setFallbackMessages(array $messages)
    {
        $this->fallbackMessages = $messages;
    }

    /**
     * Get the Presence Verifier implementation.
     *
     * @throws RuntimeException
     */
    public function getPresenceVerifier(): PresenceVerifierInterface
    {
        if (! isset($this->presenceVerifier)) {
            throw new RuntimeException('Presence verifier has not been set.');
        }

        return $this->presenceVerifier;
    }

    /**
     * Get the Presence Verifier implementation.
     *
     * @throws RuntimeException
     */
    public function getPresenceVerifierFor(?string $connection): PresenceVerifierInterface
    {
        return tap($this->getPresenceVerifier(), function ($verifier) use ($connection) {
            $verifier->setConnection($connection);
        });
    }

    /**
     * Set the Presence Verifier implementation.
     */
    public function setPresenceVerifier(PresenceVerifierInterface $presenceVerifier)
    {
        $this->presenceVerifier = $presenceVerifier;
    }

    /**
     * Get the Translator implementation.
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * Set the Translator implementation.
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Set the IoC container instance.
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Get the value of a given attribute.
     */
    public function getValue(string $attribute)
    {
        return Arr::get($this->data, $attribute);
    }

    /**
     * Set the value of a given attribute.
     *
     * @param string $attribute
     * @param mixed $value
     */
    public function setValue($attribute, $value)
    {
        Arr::set($this->data, $attribute, $value);
    }

    /**
     * Validate a given attribute against a rule.
     *
     * @param object|string $rule
     */
    protected function validateAttribute(string $attribute, $rule)
    {
        $this->currentRule = $rule;

        [$rule, $parameters] = ValidationRuleParser::parse($rule);

        if ($rule == '') {
            return;
        }

        // First we will get the correct keys for the given attribute in case the field is nested in
        // an array. Then we determine if the given rule accepts other field names as parameters.
        // If so, we will replace any asterisks found in the parameters with the correct keys.
        if (($keys = $this->getExplicitKeys($attribute))
            && $this->dependsOnOtherFields($rule)) {
            $parameters = $this->replaceAsterisksInParameters($parameters, $keys);
        }

        $value = $this->getValue($attribute);

        // If the attribute is a file, we will verify that the file upload was actually successful
        // and if it wasn't we will add a failure for the attribute. Files may not successfully
        // upload if they are too large based on PHP's settings so we will bail in this case.
        if ($value instanceof UploadedFile && ! $value->isValid()
            && $this->hasRule($attribute, array_merge($this->fileRules, $this->implicitRules))
        ) {
            return $this->addFailure($attribute, 'uploaded', []);
        }

        // If we have made it this far we will make sure the attribute is validatable and if it is
        // we will call the validation method with the attribute. If a method returns false the
        // attribute is invalid and we will add a failure message for this failing attribute.
        $validatable = $this->isValidatable($rule, $attribute, $value);

        if ($rule instanceof RuleContract) {
            return $validatable
                ? $this->validateUsingCustomRule($attribute, $value, $rule)
                : null;
        }

        $method = "validate{$rule}";

        if ($validatable && ! $this->{$method}($attribute, $value, $parameters, $this)) {
            $this->addFailure($attribute, $rule, $parameters);
        }
    }

    /**
     * Determine if the given rule depends on other fields.
     *
     * @param object|string $rule
     */
    protected function dependsOnOtherFields($rule): bool
    {
        return in_array($rule, $this->dependentRules);
    }

    /**
     * Get the explicit keys from an attribute flattened with dot notation.
     *
     * E.g. 'foo.1.bar.spark.baz' -> [1, 'spark'] for 'foo.*.bar.*.baz'
     */
    protected function getExplicitKeys(string $attribute): array
    {
        $pattern = str_replace('\*', '([^\.]+)', preg_quote($this->getPrimaryAttribute($attribute), '/'));

        if (preg_match('/^' . $pattern . '/', $attribute, $keys)) {
            array_shift($keys);

            return $keys;
        }

        return [];
    }

    /**
     * Get the primary attribute name.
     *
     * For example, if "name.0" is given, "name.*" will be returned.
     */
    protected function getPrimaryAttribute(string $attribute): string
    {
        foreach ($this->implicitAttributes as $unparsed => $parsed) {
            if (in_array($attribute, $parsed)) {
                return $unparsed;
            }
        }

        return $attribute;
    }

    /**
     * Replace each field parameter which has asterisks with the given keys.
     */
    protected function replaceAsterisksInParameters(array $parameters, array $keys): array
    {
        return array_map(fn ($field) => vsprintf(str_replace('*', '%s', $field), $keys), $parameters);
    }

    /**
     * Determine if the attribute is validatable.
     *
     * @param object|string $rule
     * @param mixed $value
     */
    protected function isValidatable($rule, string $attribute, $value): bool
    {
        return $this->presentOrRuleIsImplicit($rule, $attribute, $value)
            && $this->passesOptionalCheck($attribute)
            && $this->isNotNullIfMarkedAsNullable($rule, $attribute)
            && $this->hasNotFailedPreviousRuleIfPresenceRule($rule, $attribute);
    }

    /**
     * Determine if the field is present, or the rule implies required.
     *
     * @param object|string $rule
     * @param mixed $value
     */
    protected function presentOrRuleIsImplicit($rule, string $attribute, $value): bool
    {
        if (is_string($value) && trim($value) === '') {
            return $this->isImplicit($rule);
        }

        return $this->validatePresent($attribute, $value)
            || $this->isImplicit($rule);
    }

    /**
     * Determine if a given rule implies the attribute is required.
     *
     * @param object|string $rule
     */
    protected function isImplicit($rule): bool
    {
        return $rule instanceof ImplicitRule
            || in_array($rule, $this->implicitRules);
    }

    /**
     * Determine if the attribute passes any optional check.
     */
    protected function passesOptionalCheck(string $attribute): bool
    {
        if (! $this->hasRule($attribute, ['Sometimes'])) {
            return true;
        }

        $data = ValidationData::initializeAndGatherData($attribute, $this->data);

        return array_key_exists($attribute, $data)
            || array_key_exists($attribute, $this->data);
    }

    /**
     * Determine if the attribute fails the nullable check.
     *
     * @param object|string $rule
     */
    protected function isNotNullIfMarkedAsNullable($rule, string $attribute): bool
    {
        if ($this->isImplicit($rule) || ! $this->hasRule($attribute, ['Nullable'])) {
            return true;
        }

        return ! is_null(Arr::get($this->data, $attribute, 0));
    }

    /**
     * Determine if it's a necessary presence validation.
     *
     * This is to avoid possible database type comparison errors.
     *
     * @param object|string $rule
     */
    protected function hasNotFailedPreviousRuleIfPresenceRule($rule, string $attribute): bool
    {
        return in_array($rule, ['Unique', 'Exists']) ? ! $this->messages->has($attribute) : true;
    }

    /**
     * Validate an attribute using a custom rule object.
     * @param mixed $value
     */
    protected function validateUsingCustomRule(string $attribute, $value, RuleContract $rule)
    {
        if ($rule instanceof ValidatorAwareRule) {
            $rule->setValidator($this);
        }

        if ($rule instanceof DataAwareRule) {
            $rule->setData($this->data);
        }

        if (! $rule->passes($attribute, $value)) {
            $this->failedRules[$attribute][$rule::class] = [];

            $messages = $rule->message() ? (array) $rule->message() : [$rule::class];

            foreach ($messages as $message) {
                $this->messages->add($attribute, $this->makeReplacements(
                    $message,
                    $attribute,
                    $rule::class,
                    []
                ));
            }
        }
    }

    /**
     * Check if we should stop further validations on a given attribute.
     */
    protected function shouldStopValidating(string $attribute): bool
    {
        if ($this->hasRule($attribute, ['Bail'])) {
            return $this->messages->has($attribute);
        }

        if (isset($this->failedRules[$attribute])
            && array_key_exists('uploaded', $this->failedRules[$attribute])) {
            return true;
        }

        // In case the attribute has any rule that indicates that the field is required
        // and that rule already failed then we should stop validation at this point
        // as now there is no point in calling other rules with this field empty.
        return $this->hasRule($attribute, $this->implicitRules)
            && isset($this->failedRules[$attribute])
            && array_intersect(array_keys($this->failedRules[$attribute]), $this->implicitRules);
    }

    /**
     * Generate an array of all attributes that have messages.
     */
    protected function attributesThatHaveMessages(): array
    {
        return collect($this->messages()->toArray())->map(fn ($message, $key) => explode('.', $key)[0])->unique()->flip()->all();
    }

    /**
     * Get a rule and its parameters for a given attribute.
     *
     * @param array|string|Stringable $rules
     */
    protected function getRule(string $attribute, mixed $rules): ?array
    {
        if (! array_key_exists($attribute, $this->rules)) {
            return null;
        }

        $rules = (array) $rules;

        foreach ($this->rules[$attribute] as $rule) {
            [$rule, $parameters] = ValidationRuleParser::parse($rule);

            if (in_array($rule, $rules)) {
                return [$rule, $parameters];
            }
        }
        return null;
    }

    /**
     * Call a custom validator extension.
     */
    protected function callExtension(string $rule, array $parameters): ?bool
    {
        $callback = $this->extensions[$rule];

        if (is_callable($callback)) {
            return call_user_func_array($callback, $parameters);
        }
        if (is_string($callback)) {
            return $this->callClassBasedExtension($callback, $parameters);
        }
    }

    /**
     * Call a class based validator extension.
     */
    protected function callClassBasedExtension(string $callback, array $parameters): bool
    {
        [$class, $method] = Str::parseCallback($callback, 'validate');

        return call_user_func_array([make($class), $method], $parameters);
    }
}
