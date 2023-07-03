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
namespace Hyperf\Validation\Concerns;

use Carbon\Carbon;
use Carbon\Carbon as Date;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use Exception;
use Hyperf\Collection\Arr;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\Stringable\Str;
use Hyperf\Validation\Rules\Exists;
use Hyperf\Validation\Rules\Unique;
use Hyperf\Validation\ValidationData;
use InvalidArgumentException;
use SplFileInfo;
use Throwable;

use function Hyperf\Collection\last;

trait ValidatesAttributes
{
    /**
     * Validate that an attribute was "accepted".
     *
     * This validation rule implies the attribute is "required".
     * @param mixed $value
     */
    public function validateAccepted(string $attribute, $value): bool
    {
        $acceptable = ['yes', 'on', '1', 1, true, 'true'];

        return $this->validateRequired($attribute, $value) && in_array($value, $acceptable, true);
    }

    /**
     * Validate that an attribute is an active URL.
     *
     * @param mixed $value
     */
    public function validateActiveUrl(string $attribute, $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        if ($url = parse_url($value, PHP_URL_HOST)) {
            try {
                return count(dns_get_record($url . '.', DNS_A | DNS_AAAA)) > 0;
            } catch (Exception) {
                return false;
            }
        }

        return false;
    }

    /**
     * "Break" on first validation fail.
     *
     * Always returns true, just lets us put "bail" in rules.
     */
    public function validateBail(): bool
    {
        return true;
    }

    /**
     * Validate the date is before a given date.
     *
     * @param mixed $value
     */
    public function validateBefore(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'before');

        return $this->compareDates($attribute, $value, $parameters, '<');
    }

    /**
     * Validate the date is before or equal a given date.
     *
     * @param mixed $value
     */
    public function validateBeforeOrEqual(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'before_or_equal');

        return $this->compareDates($attribute, $value, $parameters, '<=');
    }

    /**
     * Validate the date is after a given date.
     *
     * @param mixed $value
     */
    public function validateAfter(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'after');

        return $this->compareDates($attribute, $value, $parameters, '>');
    }

    /**
     * Validate the date is equal or after a given date.
     *
     * @param mixed $value
     */
    public function validateAfterOrEqual(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'after_or_equal');

        return $this->compareDates($attribute, $value, $parameters, '>=');
    }

    /**
     * Validate that an attribute contains only alphabetic characters.
     *
     * @param mixed $value
     */
    public function validateAlpha(string $attribute, $value): bool
    {
        return is_string($value) && preg_match('/^[\pL\pM]+$/u', $value);
    }

    /**
     * Validate that an attribute contains only alpha-numeric characters, dashes, and underscores.
     *
     * @param mixed $value
     */
    public function validateAlphaDash(string $attribute, $value): bool
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        return preg_match('/^[\pL\pM\pN_-]+$/u', (string) $value) > 0;
    }

    /**
     * Validate that an attribute contains only alpha-numeric characters.
     *
     * @param mixed $value
     */
    public function validateAlphaNum(string $attribute, $value): bool
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        return preg_match('/^[\pL\pM\pN]+$/u', (string) $value) > 0;
    }

    /**
     * Validate that an attribute is an array.
     *
     * @param mixed $value
     */
    public function validateArray(string $attribute, $value, array $parameters = []): bool
    {
        if (! is_array($value)) {
            return false;
        }

        if (empty($parameters)) {
            return true;
        }

        return empty(array_diff_key($value, array_fill_keys($parameters, '')));
    }

    /**
     * Validate the size of an attribute is between a set of values.
     *
     * @param mixed $value
     */
    public function validateBetween(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'between');

        $size = $this->getSize($attribute, $value);

        return $size >= $parameters[0] && $size <= $parameters[1];
    }

    /**
     * Validate that an attribute is a boolean.
     *
     * @param mixed $value
     */
    public function validateBoolean(string $attribute, $value, array $parameters = []): bool
    {
        $acceptable = [true, false, 0, 1, '0', '1'];

        if (isset($parameters[0]) && strtolower($parameters[0]) == 'strict') {
            $acceptable = [true, false];
        }

        return in_array($value, $acceptable, true);
    }

    /**
     * Validate that an attribute has a matching confirmation.
     *
     * @param mixed $value
     */
    public function validateConfirmed(string $attribute, $value): bool
    {
        return $this->validateSame($attribute, $value, [$attribute . '_confirmation']);
    }

    /**
     * Validate that an attribute is a valid date.
     *
     * @param mixed $value
     */
    public function validateDate(string $attribute, $value): bool
    {
        if ($value instanceof DateTimeInterface) {
            return true;
        }

        if ((! is_string($value) && ! is_numeric($value)) || strtotime((string) $value) === false) {
            return false;
        }

        $date = date_parse($value);
        if ($date === false
            || $date['month'] === false
            || $date['day'] === false
            || $date['year'] === false
        ) {
            return false;
        }

        return checkdate($date['month'], $date['day'], $date['year']);
    }

    /**
     * Validate that an attribute matches a date format.
     *
     * @param mixed $value
     */
    public function validateDateFormat(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'date_format');

        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        $format = $parameters[0];

        $date = DateTime::createFromFormat('!' . $format, $value);

        return $date && $date->format($format) == $value;
    }

    /**
     * Validate that an attribute is equal to another date.
     *
     * @param mixed $value
     */
    public function validateDateEquals(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'date_equals');

        return $this->compareDates($attribute, $value, $parameters, '=');
    }

    /**
     * Validate that an attribute is different from another attribute.
     *
     * @param mixed $value
     */
    public function validateDifferent(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'different');

        foreach ($parameters as $parameter) {
            if (! Arr::has($this->data, $parameter)) {
                return false;
            }

            $other = Arr::get($this->data, $parameter);

            if ($value === $other) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate that an attribute has a given number of digits.
     *
     * @param mixed $value
     */
    public function validateDigits(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'digits');

        $value = (string) $value;
        return ! preg_match('/[^0-9]/', $value)
            && strlen($value) == $parameters[0];
    }

    /**
     * Validate that an attribute is between a given number of digits.
     *
     * @param mixed $value
     */
    public function validateDigitsBetween(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'digits_between');

        $value = (string) $value;
        $length = strlen($value);

        return ! preg_match('/[^0-9]/', $value)
            && $length >= $parameters[0] && $length <= $parameters[1];
    }

    /**
     * Validate the dimensions of an image matches the given values.
     *
     * @param mixed $value
     */
    public function validateDimensions(string $attribute, $value, array $parameters): bool
    {
        if (! $this->isValidFileInstance($value) || ! $sizeDetails = @getimagesize($value->getRealPath())) {
            return false;
        }

        $this->requireParameterCount(1, $parameters, 'dimensions');

        [$width, $height] = $sizeDetails;

        $parameters = $this->parseNamedParameters($parameters);

        if ($this->failsBasicDimensionChecks($parameters, $width, $height)
            || $this->failsRatioCheck($parameters, $width, $height)) {
            return false;
        }

        return true;
    }

    /**
     * Validate an attribute is unique among other values.
     *
     * @param mixed $value
     */
    public function validateDistinct(string $attribute, $value, array $parameters): bool
    {
        $data = Arr::except($this->getDistinctValues($attribute), $attribute);

        if (in_array('ignore_case', $parameters)) {
            return empty(preg_grep('/^' . preg_quote((string) $value, '/') . '$/iu', $data));
        }

        return ! in_array($value, array_values($data));
    }

    /**
     * Validate that an attribute is a valid e-mail address.
     *
     * @param mixed $value
     */
    public function validateEmail(string $attribute, $value): bool
    {
        if (! is_string($value) && ! (is_object($value) && method_exists($value, '__toString'))) {
            return false;
        }

        return (new EmailValidator())->isValid((string) $value, new RFCValidation());
    }

    /**
     * Validate the existence of an attribute value in a database table.
     *
     * @param mixed $value
     */
    public function validateExists(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'exists');

        [$connection, $table] = $this->parseTable($parameters[0]);

        // The second parameter position holds the name of the column that should be
        // verified as existing. If this parameter is not specified we will guess
        // that the columns being "verified" shares the given attribute's name.
        $column = $this->getQueryColumn($parameters, $attribute);

        $expected = is_array($value) ? count(array_unique($value)) : 1;

        return $this->getExistCount(
            $connection,
            $table,
            $column,
            $value,
            $parameters
        ) >= $expected;
    }

    /**
     * Validate the uniqueness of an attribute value on a given database table.
     *
     * If a database column is not specified, the attribute will be used.
     *
     * @param mixed $value
     */
    public function validateUnique(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'unique');

        [$connection, $table] = $this->parseTable($parameters[0]);

        // The second parameter position holds the name of the column that needs to
        // be verified as unique. If this parameter isn't specified we will just
        // assume that this column to be verified shares the attribute's name.
        $column = $this->getQueryColumn($parameters, $attribute);

        [$idColumn, $id] = [null, null];

        if (isset($parameters[2])) {
            [$idColumn, $id] = $this->getUniqueIds($parameters);

            if (! is_null($id)) {
                $id = stripslashes((string) $id);
            }
        }

        // The presence verifier is responsible for counting rows within this store
        // mechanism which might be a relational database or any other permanent
        // data store like Redis, etc. We will use it to determine uniqueness.
        $verifier = $this->getPresenceVerifierFor($connection);

        $extra = $this->getUniqueExtra($parameters);

        if ($this->currentRule instanceof Unique) {
            $extra = array_merge($extra, $this->currentRule->queryCallbacks());
        }

        return $verifier->getCount(
            $table,
            $column,
            $value,
            $id,
            $idColumn,
            $extra
        ) == 0;
    }

    /**
     * Parse the connection / table for the unique / exists rules.
     */
    public function parseTable(string $table): array
    {
        return Str::contains($table, '.') ? explode('.', $table, 2) : [null, $table];
    }

    /**
     * Get the column name for an exists / unique query.
     */
    public function getQueryColumn(array $parameters, string $attribute): string
    {
        return isset($parameters[1]) && $parameters[1] !== 'NULL'
            ? $parameters[1] : $this->guessColumnForQuery($attribute);
    }

    /**
     * Guess the database column from the given attribute name.
     */
    public function guessColumnForQuery(string $attribute): string
    {
        if (in_array($attribute, Arr::collapse($this->implicitAttributes))
            && ! is_numeric($last = last(explode('.', $attribute)))) {
            return $last;
        }

        return $attribute;
    }

    /**
     * Validate the given value is a valid file.
     *
     * @param mixed $value
     */
    public function validateFile(string $attribute, $value): bool
    {
        return $this->isValidFileInstance($value);
    }

    /**
     * Validate the given attribute is filled if it is present.
     *
     * @param mixed $value
     */
    public function validateFilled(string $attribute, $value): bool
    {
        if (Arr::has($this->data, $attribute)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute is greater than another attribute.
     *
     * @param mixed $value
     */
    public function validateGt(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'gt');

        $comparedToValue = $this->getValue($parameters[0]);

        $this->shouldBeNumeric($attribute, 'Gt');

        if (is_null($comparedToValue) && (is_numeric($value) && is_numeric($parameters[0]))) {
            return $this->getSize($attribute, $value) > $parameters[0];
        }

        if ($this->hasRule($attribute, $this->numericRules) && is_numeric($value) && is_numeric($comparedToValue)) {
            return $value > $comparedToValue;
        }

        if (! $this->isSameType($value, $comparedToValue)) {
            return false;
        }

        return $this->getSize($attribute, $value) > $this->getSize($attribute, $comparedToValue);
    }

    /**
     * Validate that an attribute is less than another attribute.
     *
     * @param mixed $value
     */
    public function validateLt(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'lt');

        $comparedToValue = $this->getValue($parameters[0]);

        $this->shouldBeNumeric($attribute, 'Lt');

        if (is_null($comparedToValue) && (is_numeric($value) && is_numeric($parameters[0]))) {
            return $this->getSize($attribute, $value) < $parameters[0];
        }

        if ($this->hasRule($attribute, $this->numericRules) && is_numeric($value) && is_numeric($comparedToValue)) {
            return $value < $comparedToValue;
        }

        if (! $this->isSameType($value, $comparedToValue)) {
            return false;
        }

        return $this->getSize($attribute, $value) < $this->getSize($attribute, $comparedToValue);
    }

    /**
     * Validate that an attribute is greater than or equal another attribute.
     *
     * @param mixed $value
     */
    public function validateGte(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'gte');

        $comparedToValue = $this->getValue($parameters[0]);

        $this->shouldBeNumeric($attribute, 'Gte');

        if (is_null($comparedToValue) && (is_numeric($value) && is_numeric($parameters[0]))) {
            return $this->getSize($attribute, $value) >= $parameters[0];
        }

        if ($this->hasRule($attribute, $this->numericRules) && is_numeric($value) && is_numeric($comparedToValue)) {
            return $value >= $comparedToValue;
        }

        if (! $this->isSameType($value, $comparedToValue)) {
            return false;
        }

        return $this->getSize($attribute, $value) >= $this->getSize($attribute, $comparedToValue);
    }

    /**
     * Validate that an attribute is less than or equal another attribute.
     *
     * @param mixed $value
     */
    public function validateLte(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'lte');

        $comparedToValue = $this->getValue($parameters[0]);

        $this->shouldBeNumeric($attribute, 'Lte');

        if (is_null($comparedToValue) && (is_numeric($value) && is_numeric($parameters[0]))) {
            return $this->getSize($attribute, $value) <= $parameters[0];
        }

        if ($this->hasRule($attribute, $this->numericRules) && is_numeric($value) && is_numeric($comparedToValue)) {
            return $value <= $comparedToValue;
        }

        if (! $this->isSameType($value, $comparedToValue)) {
            return false;
        }

        return $this->getSize($attribute, $value) <= $this->getSize($attribute, $comparedToValue);
    }

    /**
     * Validate the MIME type of a file is an image MIME type.
     *
     * @param mixed $value
     */
    public function validateImage(string $attribute, $value): bool
    {
        return $this->validateMimes($attribute, $value, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp']);
    }

    /**
     * Validate an attribute is contained within a list of values.
     *
     * @param mixed $value
     */
    public function validateIn(string $attribute, $value, array $parameters): bool
    {
        if (is_array($value) && $this->hasRule($attribute, 'Array')) {
            foreach ($value as $element) {
                if (is_array($element)) {
                    return false;
                }
            }

            return count(array_diff($value, $parameters)) === 0;
        }

        return ! is_array($value) && in_array((string) $value, $parameters, true);
    }

    /**
     * Validate that the values of an attribute is in another attribute.
     *
     * @param mixed $value
     */
    public function validateInArray(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'in_array');

        $explicitPath = ValidationData::getLeadingExplicitAttributePath($parameters[0]);

        $attributeData = ValidationData::extractDataFromPath($explicitPath, $this->data);

        $otherValues = Arr::where(Arr::dot($attributeData), fn ($value, $key) => Str::is($parameters[0], $key));

        return in_array($value, $otherValues);
    }

    /**
     * Validate that an attribute is an integer.
     *
     * @param mixed $value
     */
    public function validateInteger(string $attribute, $value, array $parameters = []): bool
    {
        if (isset($parameters[0]) && strtolower($parameters[0]) == 'strict' && gettype($value) != 'integer') {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validate that an attribute is a valid IP.
     *
     * @param mixed $value
     */
    public function validateIp(string $attribute, $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Validate that an attribute is a valid IPv4.
     *
     * @param mixed $value
     */
    public function validateIpv4(string $attribute, $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * Validate that an attribute is a valid IPv6.
     *
     * @param mixed $value
     */
    public function validateIpv6(string $attribute, $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Validate the attribute is a valid JSON string.
     *
     * @param mixed $value
     */
    public function validateJson(string $attribute, $value): bool
    {
        if (! is_scalar($value) && ! method_exists($value, '__toString')) {
            return false;
        }

        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Validate the size of an attribute is less than a maximum value.
     *
     * @param mixed $value
     */
    public function validateMax(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'max');

        if ($value instanceof UploadedFile && ! $value->isValid()) {
            return false;
        }

        return $this->getSize($attribute, $value) <= $parameters[0];
    }

    /**
     * Validate the guessed extension of a file upload is in a set of file extensions.
     *
     * @param SplFileInfo $value
     */
    public function validateMimes(string $attribute, $value, array $parameters): bool
    {
        if (! $this->isValidFileInstance($value)) {
            return false;
        }

        if ($this->shouldBlockPhpUpload($value, $parameters)) {
            return false;
        }

        if (empty($value->getPath())) {
            return false;
        }

        if (in_array(strtolower($value->getExtension()), $parameters)) {
            return true;
        }

        if ($value instanceof UploadedFile) {
            return in_array($value->getMimeType(), $parameters);
        }

        return false;
    }

    /**
     * Validate the MIME type of a file upload attribute is in a set of MIME types.
     *
     * @param SplFileInfo $value
     */
    public function validateMimetypes(string $attribute, $value, array $parameters): bool
    {
        if (! $this->isValidFileInstance($value)) {
            return false;
        }

        if ($this->shouldBlockPhpUpload($value, $parameters)) {
            return false;
        }

        return $value->getPath() !== ''
            && (in_array($value->getMimeType(), $parameters)
                || in_array(explode('/', $value->getMimeType())[0] . '/*', $parameters));
    }

    /**
     * Validate the size of an attribute is greater than a minimum value.
     *
     * @param mixed $value
     */
    public function validateMin(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'min');

        return $this->getSize($attribute, $value) >= $parameters[0];
    }

    /**
     * "Indicate" validation should pass if value is null.
     *
     * Always returns true, just lets us put "nullable" in rules.
     */
    public function validateNullable(): bool
    {
        return true;
    }

    /**
     * Validate an attribute is not contained within a list of values.
     *
     * @param mixed $value
     */
    public function validateNotIn(string $attribute, $value, array $parameters): bool
    {
        return ! $this->validateIn($attribute, $value, $parameters);
    }

    /**
     * Validate that an attribute is numeric.
     *
     * @param mixed $value
     */
    public function validateNumeric(string $attribute, $value): bool
    {
        return is_numeric($value);
    }

    /**
     * Validate that an attribute exists even if not filled.
     *
     * @param mixed $value
     */
    public function validatePresent(string $attribute, $value): bool
    {
        return Arr::has($this->data, $attribute);
    }

    /**
     * Validate that an attribute passes a regular expression check.
     *
     * @param mixed $value
     */
    public function validateRegex(string $attribute, $value, array $parameters): bool
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        $this->requireParameterCount(1, $parameters, 'regex');

        return preg_match($parameters[0], (string) $value) > 0;
    }

    /**
     * Validate that an attribute does not pass a regular expression check.
     *
     * @param mixed $value
     */
    public function validateNotRegex(string $attribute, $value, array $parameters): bool
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        $this->requireParameterCount(1, $parameters, 'not_regex');

        return preg_match($parameters[0], $value) < 1;
    }

    /**
     * Validate that a required attribute exists.
     *
     * @param mixed $value
     */
    public function validateRequired(string $attribute, $value): bool
    {
        if (is_null($value)) {
            return false;
        }
        if (is_string($value) && trim($value) === '') {
            return false;
        }
        if (is_countable($value) && count($value) < 1) {
            return false;
        }
        if ($value instanceof SplFileInfo) {
            return (string) $value->getPath() !== '';
        }

        return true;
    }

    /**
     * Validate that an attribute exists when another attribute has a given value.
     *
     * @param mixed $value
     */
    public function validateRequiredIf(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'required_if');

        $other = Arr::get($this->data, $parameters[0]);

        $values = array_slice($parameters, 1);

        if (is_bool($other)) {
            $values = $this->convertValuesToBoolean($values);
        }

        if (in_array($other, $values)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute exists when another attribute does not have a given value.
     *
     * @param mixed $value
     */
    public function validateRequiredUnless(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'required_unless');

        $data = Arr::get($this->data, $parameters[0]);

        $values = array_slice($parameters, 1);

        if (! in_array($data, $values)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute exists when any other attribute exists.
     *
     * @param mixed $value
     */
    public function validateRequiredWith(string $attribute, $value, array $parameters): bool
    {
        if (! $this->allFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute exists when all other attributes exists.
     *
     * @param mixed $value
     */
    public function validateRequiredWithAll(string $attribute, $value, array $parameters): bool
    {
        if (! $this->anyFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute exists when another attribute does not.
     *
     * @param mixed $value
     */
    public function validateRequiredWithout(string $attribute, $value, array $parameters): bool
    {
        if ($this->anyFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute exists when all other attributes do not.
     *
     * @param mixed $value
     */
    public function validateRequiredWithoutAll(string $attribute, $value, array $parameters): bool
    {
        if ($this->allFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that two attributes match.
     *
     * @param mixed $value
     */
    public function validateSame(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'same');

        $other = Arr::get($this->data, $parameters[0]);

        return $value === $other;
    }

    /**
     * Validate the size of an attribute.
     *
     * @param mixed $value
     */
    public function validateSize(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'size');

        return $this->getSize($attribute, $value) == $parameters[0];
    }

    /**
     * "Validate" optional attributes.
     *
     * Always returns true, just lets us put sometimes in rules.
     */
    public function validateSometimes()
    {
        return true;
    }

    /**
     * Validate the attribute starts with a given substring.
     *
     * @param mixed $value
     */
    public function validateStartsWith(string $attribute, $value, array $parameters): bool
    {
        return Str::startsWith($value, $parameters);
    }

    /**
     * Validate the attribute ends with a given substring.
     *
     * @param mixed $value
     */
    public function validateEndsWith(string $attribute, $value, array $parameters): bool
    {
        return Str::endsWith($value, $parameters);
    }

    /**
     * Validate that an attribute is a string.
     *
     * @param mixed $value
     */
    public function validateString(string $attribute, $value): bool
    {
        return is_string($value);
    }

    /**
     * Validate that an attribute is a valid timezone.
     *
     * @param mixed $value
     */
    public function validateTimezone(string $attribute, $value): bool
    {
        try {
            new DateTimeZone($value);
        } catch (Throwable) {
            return false;
        }

        return true;
    }

    /**
     * Validate that an attribute is a valid URL.
     *
     * @param mixed $value
     */
    public function validateUrl(string $attribute, $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        /*
         * This pattern is derived from Symfony\Component\Validator\Constraints\UrlValidator (2.7.4).
         *
         * (c) Fabien Potencier <fabien@symfony.com> http://symfony.com
         */
        $pattern = '~^
            ((aaa|aaas|about|acap|acct|acr|adiumxtra|afp|afs|aim|apt|attachment|aw|barion|beshare|bitcoin|blob|bolo|callto|cap|chrome|chrome-extension|cid|coap|coaps|com-eventbrite-attendee|content|crid|cvs|data|dav|dict|dlna-playcontainer|dlna-playsingle|dns|dntp|dtn|dvb|ed2k|example|facetime|fax|feed|feedready|file|filesystem|finger|fish|ftp|geo|gg|git|gizmoproject|go|gopher|gtalk|h323|ham|hcp|http|https|iax|icap|icon|im|imap|info|iotdisco|ipn|ipp|ipps|irc|irc6|ircs|iris|iris.beep|iris.lwz|iris.xpc|iris.xpcs|itms|jabber|jar|jms|keyparc|lastfm|ldap|ldaps|magnet|mailserver|mailto|maps|market|message|mid|mms|modem|ms-help|ms-settings|ms-settings-airplanemode|ms-settings-bluetooth|ms-settings-camera|ms-settings-cellular|ms-settings-cloudstorage|ms-settings-emailandaccounts|ms-settings-language|ms-settings-location|ms-settings-lock|ms-settings-nfctransactions|ms-settings-notifications|ms-settings-power|ms-settings-privacy|ms-settings-proximity|ms-settings-screenrotation|ms-settings-wifi|ms-settings-workplace|msnim|msrp|msrps|mtqp|mumble|mupdate|mvn|news|nfs|ni|nih|nntp|notes|oid|opaquelocktoken|pack|palm|paparazzi|pkcs11|platform|pop|pres|prospero|proxy|psyc|query|redis|rediss|reload|res|resource|rmi|rsync|rtmfp|rtmp|rtsp|rtsps|rtspu|secondlife|s3|service|session|sftp|sgn|shttp|sieve|sip|sips|skype|smb|sms|smtp|snews|snmp|soap.beep|soap.beeps|soldat|spotify|ssh|steam|stun|stuns|submit|svn|tag|teamspeak|tel|teliaeid|telnet|tftp|things|thismessage|tip|tn3270|turn|turns|tv|udp|unreal|urn|ut2004|vemmi|ventrilo|videotex|view-source|wais|webcal|ws|wss|wtai|wyciwyg|xcon|xcon-userid|xfire|xmlrpc\.beep|xmlrpc.beeps|xmpp|xri|ymsgr|z39\.50|z39\.50r|z39\.50s))://                                 # protocol
            (([\pL\pN-]+:)?([\pL\pN-]+)@)?          # basic auth
            (
                ([\pL\pN\pS\-\.])+(\.?([\pL]|xn\-\-[\pL\pN-]+)+\.?) # a domain name
                    |                                              # or
                \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}                 # an IP address
                    |                                              # or
                \[
                    (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                \]  # an IPv6 address
            )
            (:[0-9]+)?                              # a port (optional)
            (/?|/\S+|\?\S*|\#\S*)                   # a /, nothing, a / with something, a query or a fragment
        $~ixu';

        return preg_match($pattern, $value) > 0;
    }

    /**
     * Validate that an attribute is a valid UUID.
     *
     * @param mixed $value
     */
    public function validateUuid(string $attribute, $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return preg_match('/^[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}$/iD', $value) > 0;
    }

    /**
     * Check that the given value is a valid file instance.
     *
     * @param mixed $value
     */
    public function isValidFileInstance($value): bool
    {
        if ($value instanceof UploadedFile && ! $value->isValid()) {
            return false;
        }

        return $value instanceof SplFileInfo;
    }

    /**
     * Require a certain number of parameters to be present.
     *
     * @throws InvalidArgumentException
     */
    public function requireParameterCount(int $count, array $parameters, string $rule)
    {
        if (count($parameters) < $count) {
            throw new InvalidArgumentException("Validation rule {$rule} requires at least {$count} parameters.");
        }
    }

    /**
     * Compare a given date against another using an operator.
     *
     * @param mixed $value
     */
    protected function compareDates(string $attribute, $value, array $parameters, string $operator): bool
    {
        if (! is_string($value) && ! is_numeric($value) && ! $value instanceof DateTimeInterface) {
            return false;
        }

        if ($format = $this->getDateFormat($attribute)) {
            return $this->checkDateTimeOrder($format, $value, $parameters[0], $operator);
        }

        if (! $date = $this->getDateTimestamp($parameters[0])) {
            $date = $this->getDateTimestamp($this->getValue($parameters[0]));
        }

        return $this->compare($this->getDateTimestamp($value), $date, $operator);
    }

    /**
     * Get the date format for an attribute if it has one.
     *
     * @return null|string
     */
    protected function getDateFormat(string $attribute)
    {
        if ($result = $this->getRule($attribute, 'DateFormat')) {
            return $result[1][0];
        }
    }

    /**
     * Get the date timestamp.
     *
     * @param mixed $value
     */
    protected function getDateTimestamp($value): bool|int
    {
        if ($value instanceof DateTimeInterface) {
            return $value->getTimestamp();
        }

        if ($this->isTestingRelativeDateTime($value)) {
            $date = $this->getDateTime($value);

            if (! is_null($date)) {
                return $date->getTimestamp();
            }
        }

        return strtotime((string) $value);
    }

    /**
     * Given two date/time strings, check that one is after the other.
     */
    protected function checkDateTimeOrder(string $format, string $first, string $second, string $operator): bool
    {
        $firstDate = $this->getDateTimeWithOptionalFormat($format, $first);

        if (! $secondDate = $this->getDateTimeWithOptionalFormat($format, $second)) {
            $secondDate = $this->getDateTimeWithOptionalFormat($format, $this->getValue($second));
        }

        return ($firstDate && $secondDate) && $this->compare($firstDate, $secondDate, $operator);
    }

    /**
     * Get a DateTime instance from a string.
     *
     * @return null|DateTime
     */
    protected function getDateTimeWithOptionalFormat(string $format, ?string $value)
    {
        if (is_null($value)) {
            return null;
        }
        if ($date = DateTime::createFromFormat('!' . $format, $value)) {
            return $date;
        }

        return $this->getDateTime($value);
    }

    /**
     * Get a DateTime instance from a string with no format.
     *
     * @return null|DateTime
     */
    protected function getDateTime(string $value)
    {
        try {
            if ($this->isTestingRelativeDateTime($value)) {
                return Date::parse($value);
            }

            return new DateTime($value);
        } catch (Exception) {
        }
    }

    /**
     * Check if the given value should be adjusted to Carbon::getTestNow().
     *
     * @param mixed $value
     */
    protected function isTestingRelativeDateTime($value): bool
    {
        return Carbon::hasTestNow() && is_string($value) && (
            $value === 'now' || Carbon::hasRelativeKeywords($value)
        );
    }

    /**
     * Test if the given width and height fail any conditions.
     */
    protected function failsBasicDimensionChecks(array $parameters, int $width, int $height): bool
    {
        return (isset($parameters['width']) && $parameters['width'] != $width)
            || (isset($parameters['min_width']) && $parameters['min_width'] > $width)
            || (isset($parameters['max_width']) && $parameters['max_width'] < $width)
            || (isset($parameters['height']) && $parameters['height'] != $height)
            || (isset($parameters['min_height']) && $parameters['min_height'] > $height)
            || (isset($parameters['max_height']) && $parameters['max_height'] < $height);
    }

    /**
     * Determine if the given parameters fail a dimension ratio check.
     */
    protected function failsRatioCheck(array $parameters, int $width, int $height): bool
    {
        if (! isset($parameters['ratio'])) {
            return false;
        }

        [$numerator, $denominator] = array_replace(
            [1, 1],
            array_filter(sscanf($parameters['ratio'], '%f/%d'))
        );

        $precision = 1 / max($width, $height);

        return abs($numerator / $denominator - $width / $height) > $precision;
    }

    /**
     * Get the values to distinct between.
     */
    protected function getDistinctValues(string $attribute): array
    {
        $attributeName = $this->getPrimaryAttribute($attribute);

        if (! property_exists($this, 'distinctValues')) {
            return $this->extractDistinctValues($attributeName);
        }

        if (! array_key_exists($attributeName, $this->distinctValues)) {
            $this->distinctValues[$attributeName] = $this->extractDistinctValues($attributeName);
        }

        return $this->distinctValues[$attributeName];
    }

    /**
     * Extract the distinct values from the data.
     */
    protected function extractDistinctValues(string $attribute): array
    {
        $attributeData = ValidationData::extractDataFromPath(
            ValidationData::getLeadingExplicitAttributePath($attribute),
            $this->data
        );

        $pattern = str_replace('\*', '[^.]+', preg_quote($attribute, '#'));

        return Arr::where(Arr::dot($attributeData), fn ($value, $key) => (bool) preg_match('#^' . $pattern . '\z#u', $key));
    }

    /**
     * Get the number of records that exist in storage.
     *
     * @param mixed $connection
     * @param mixed $value
     */
    protected function getExistCount($connection, string $table, string $column, $value, array $parameters): int
    {
        $verifier = $this->getPresenceVerifierFor($connection);

        $extra = $this->getExtraConditions(
            array_values(array_slice($parameters, 2))
        );

        if ($this->currentRule instanceof Exists) {
            $extra = array_merge($extra, $this->currentRule->queryCallbacks());
        }

        return is_array($value)
            ? $verifier->getMultiCount($table, $column, $value, $extra)
            : $verifier->getCount($table, $column, $value, null, null, $extra);
    }

    /**
     * Get the excluded ID column and value for the unique rule.
     */
    protected function getUniqueIds(array $parameters): array
    {
        $idColumn = $parameters[3] ?? 'id';

        return [$idColumn, $this->prepareUniqueId($parameters[2])];
    }

    /**
     * Prepare the given ID for querying.
     *
     * @param mixed $id
     * @return int
     */
    protected function prepareUniqueId($id)
    {
        if (preg_match('/\[(.*)\]/', $id, $matches)) {
            $id = $this->getValue($matches[1]);
        }

        if (strtolower((string) $id) === 'null') {
            $id = null;
        }

        if (filter_var($id, FILTER_VALIDATE_INT) !== false) {
            $id = (int) $id;
        }

        return $id;
    }

    /**
     * Get the extra conditions for a unique rule.
     */
    protected function getUniqueExtra(array $parameters): array
    {
        if (isset($parameters[4])) {
            return $this->getExtraConditions(array_slice($parameters, 4));
        }

        return [];
    }

    /**
     * Get the extra conditions for a unique / exists rule.
     */
    protected function getExtraConditions(array $segments): array
    {
        $extra = [];

        $count = count($segments);

        for ($i = 0; $i < $count; $i += 2) {
            $extra[$segments[$i]] = $segments[$i + 1];
        }

        return $extra;
    }

    /**
     * Check if PHP uploads are explicitly allowed.
     *
     * @param SplFileInfo $value
     */
    protected function shouldBlockPhpUpload($value, array $parameters): bool
    {
        if (in_array('php', $parameters)) {
            return false;
        }

        $phpExtensions = [
            'php', 'php3', 'php4', 'php5', 'phtml',
        ];

        return in_array(trim(strtolower($value->getExtension())), $phpExtensions);
    }

    /**
     * Convert the given values to boolean if they are string "true" / "false".
     */
    protected function convertValuesToBoolean(array $values): array
    {
        return array_map(function ($value) {
            if ($value === 'true') {
                return true;
            }
            if ($value === 'false') {
                return false;
            }

            return $value;
        }, $values);
    }

    /**
     * Determine if any of the given attributes fail the required test.
     */
    protected function anyFailingRequired(array $attributes): bool
    {
        foreach ($attributes as $key) {
            if (! $this->validateRequired($key, $this->getValue($key))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if all of the given attributes fail the required test.
     */
    protected function allFailingRequired(array $attributes): bool
    {
        foreach ($attributes as $key) {
            if ($this->validateRequired($key, $this->getValue($key))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the size of an attribute.
     *
     * @param mixed $value
     */
    protected function getSize(string $attribute, $value): float|int|string
    {
        $hasNumeric = $this->hasRule($attribute, $this->numericRules);

        // This method will determine if the attribute is a number, string, or file and
        // return the proper size accordingly. If it is a number, then number itself
        // is the size. If it is a file, we take kilobytes, and for a string the
        // entire length of the string will be considered the attribute size.
        if (is_numeric($value) && $hasNumeric) {
            return $value;
        }
        if (is_array($value)) {
            return count($value);
        }
        if ($value instanceof SplFileInfo) {
            return $value->getSize() / 1024;
        }

        return mb_strlen((string) $value);
    }

    /**
     * Determine if a comparison passes between the given values.
     *
     * @param mixed $first
     * @param mixed $second
     * @throws InvalidArgumentException
     */
    protected function compare($first, $second, string $operator): bool
    {
        return match ($operator) {
            '<' => $first < $second,
            '>' => $first > $second,
            '<=' => $first <= $second,
            '>=' => $first >= $second,
            '=' => $first == $second,
            default => throw new InvalidArgumentException(),
        };
    }

    /**
     * Parse named parameters to $key => $value items.
     */
    protected function parseNamedParameters(array $parameters): ?array
    {
        return array_reduce($parameters, function ($result, $item) {
            [$key, $value] = array_pad(explode('=', $item, 2), 2, null);

            $result[$key] = $value;

            return $result;
        });
    }

    /**
     * Check if the parameters are of the same type.
     *
     * @param mixed $first
     * @param mixed $second
     */
    protected function isSameType($first, $second): bool
    {
        return gettype($first) == gettype($second);
    }

    /**
     * Adds the existing rule to the numericRules array if the attribute's value is numeric.
     */
    protected function shouldBeNumeric(string $attribute, string $rule)
    {
        if (is_numeric($this->getValue($attribute))) {
            $this->numericRules[] = $rule;
        }
    }
}
