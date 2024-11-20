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

use Brick\Math\BigDecimal;
use Brick\Math\Exception\MathException as BrickMathException;
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
use Stringable;
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

    public function validateAcceptedIf(string $attribute, mixed $value, $parameters): bool
    {
        $acceptable = ['yes', 'on', '1', 1, true, 'true'];

        $this->requireParameterCount(2, $parameters, 'accepted_if');

        [$values, $other] = $this->parseDependentRuleParameters($parameters);

        if (in_array($other, $values, is_bool($other) || is_null($other))) {
            return $this->validateRequired($attribute, $value) && in_array($value, $acceptable, true);
        }

        return true;
    }

    /**
     * Validate that an attribute was "declined".
     *
     * This validation rule implies the attribute is "required".
     */
    public function validateDeclined(string $attribute, mixed $value): bool
    {
        $acceptable = ['no', 'off', '0', 0, false, 'false'];

        return $this->validateRequired($attribute, $value) && in_array($value, $acceptable, true);
    }

    /**
     * Validate that an attribute was "declined" when another attribute has a given value.
     */
    public function validateDeclinedIf(string $attribute, mixed $value, mixed $parameters): bool
    {
        $acceptable = ['no', 'off', '0', 0, false, 'false'];

        $this->requireParameterCount(2, $parameters, 'declined_if');

        [$values, $other] = $this->parseDependentRuleParameters($parameters);

        if (in_array($other, $values, is_bool($other) || is_null($other))) {
            return $this->validateRequired($attribute, $value) && in_array($value, $acceptable, true);
        }

        return true;
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
     * Validate that an attribute is 7 bit ASCII.
     */
    public function validateAscii(string $attribute, mixed $value): bool
    {
        if ($value === '') {
            return true;
        }
        return ! \preg_match('/[^\x00-\x7F]/', (string) $value);
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
     * @param mixed $parameters
     */
    public function validateAlpha(string $attribute, mixed $value, array $parameters): bool
    {
        if (isset($parameters[0]) && $parameters[0] === 'ascii') {
            return is_string($value) && preg_match('/\A[a-zA-Z]+\z/u', $value);
        }

        return is_string($value) && preg_match('/\A[\pL\pM]+\z/u', $value);
    }

    /**
     * Validate that an attribute contains only alpha-numeric characters, dashes, and underscores.
     * @param mixed $attribute
     * @param mixed $parameters
     */
    public function validateAlphaDash(string $attribute, mixed $value, array $parameters): bool
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        if (isset($parameters[0]) && $parameters[0] === 'ascii') {
            return preg_match('/\A[a-zA-Z0-9_-]+\z/u', (string) $value) > 0;
        }

        return preg_match('/\A[\pL\pM\pN_-]+\z/u', (string) $value) > 0;
    }

    /**
     * Validate that an attribute contains only alpha-numeric characters.
     * If the 'ascii' option is passed, validate that an attribute contains only ascii alpha-numeric characters.
     *
     * @param mixed $parameters
     */
    public function validateAlphaNum(string $attribute, mixed $value, array $parameters): bool
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        if (isset($parameters[0]) && $parameters[0] === 'ascii') {
            return preg_match('/\A[a-zA-Z0-9]+\z/u', (string) $value) > 0;
        }

        return preg_match('/\A[\pL\pM\pN]+\z/u', (string) $value) > 0;
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
     * Validate that an attribute is a list.
     */
    public function validateList(string $attribute, mixed $value): bool
    {
        return is_array($value) && array_is_list($value);
    }

    /**
     * Validate that an array has all of the given keys.
     *
     * @param array<int, int|string> $parameters
     */
    public function validateRequiredArrayKeys(string $attribute, mixed $value, array $parameters): bool
    {
        if (! is_array($value)) {
            return false;
        }

        foreach ($parameters as $param) {
            if (! Arr::exists($value, $param)) {
                return false;
            }
        }

        return true;
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
     * Validate that an attribute has a given number of decimal places.
     *
     * @param array<int, int|string> $parameters
     */
    public function validateDecimal(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'decimal');

        if (! $this->validateNumeric($attribute, $value)) {
            return false;
        }

        $matches = [];

        if (preg_match('/^[+-]?\d*\.?(\d*)$/', (string) $value, $matches) !== 1) {
            return false;
        }

        $decimals = strlen(end($matches));

        if (! isset($parameters[1])) {
            return $decimals == $parameters[0];
        }

        return $decimals >= $parameters[0]
            && $decimals <= $parameters[1];
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
     */
    public function validateDimensions(string $attribute, mixed $value, array $parameters): bool
    {
        if ($this->isValidFileInstance($value) && in_array($value->getMimeType(), ['image/svg+xml', 'image/svg'])) {
            return true;
        }

        if (! $this->isValidFileInstance($value)) {
            return false;
        }

        $dimensions = method_exists($value, 'dimensions')
            ? $value->dimensions()
            : @getimagesize($value->getRealPath());

        if (! $dimensions) {
            return false;
        }

        $this->requireParameterCount(1, $parameters, 'dimensions');

        [$width, $height] = $dimensions;

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
     * Validate that an attribute is lowercase.
     *
     * @param array<int, int|string> $parameters
     */
    public function validateLowercase(string $attribute, mixed $value, array $parameters): bool
    {
        return Str::lower($value) === $value;
    }

    /**
     * Validate that an attribute is uppercase.
     *
     * @param array<int, int|string> $parameters
     */
    public function validateUppercase(string $attribute, mixed $value, array $parameters): bool
    {
        return Str::upper($value) === $value;
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
     * Validate that an attribute is a valid MAC address.
     */
    public function validateMacAddress(string $attribute, mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_MAC) !== false;
    }

    /**
     * Validate the attribute is a valid JSON string.
     *
     * @param mixed $value
     */
    public function validateJson(string $attribute, $value): bool
    {
        if ($value instanceof Stringable) {
            $value = (string) $value;
        }

        if (! is_string($value)) {
            return false;
        }

        if (function_exists('json_validate')) {
            return json_validate($value);
        }

        try {
            json_decode($value, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return false;
        }

        return true;
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
     * Validate that an attribute has a maximum number of digits.
     *
     * @param array<int, int|string> $parameters
     */
    public function validateMaxDigits(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'max_digits');

        $value = (string) $value;
        $length = strlen($value);

        return ! preg_match('/[^0-9]/', $value) && $length <= $parameters[0];
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
     * Validate that an attribute has a minimum number of digits.
     *
     * @param array<int, int|string> $parameters
     */
    public function validateMinDigits(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'min_digits');

        $value = (string) $value;
        $length = strlen($value);

        return ! preg_match('/[^0-9]/', $value) && $length >= $parameters[0];
    }

    /**
     * Validate that an attribute is missing.
     *
     * @param array<int, int|string> $parameters
     */
    public function validateMissing(string $attribute, mixed $value, array $parameters): bool
    {
        return ! Arr::has($this->data, $attribute);
    }

    /**
     * Validate that an attribute is missing when another attribute has a given value.
     *
     * @param array<int, int|string> $parameters
     */
    public function validateMissingIf(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'missing_if');

        [$values, $other] = $this->parseDependentRuleParameters($parameters);

        if (in_array($other, $values, is_bool($other) || is_null($other))) {
            return $this->validateMissing($attribute, $value, $parameters);
        }

        return true;
    }

    /**
     * Validate that an attribute is missing unless another attribute has a given value.
     *
     * @param array<int, int|string> $parameters
     */
    public function validateMissingUnless(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'missing_unless');

        [$values, $other] = $this->parseDependentRuleParameters($parameters);

        if (! in_array($other, $values, is_bool($other) || is_null($other))) {
            return $this->validateMissing($attribute, $value, $parameters);
        }

        return true;
    }

    /**
     * Validate that an attribute is missing when any given attribute is present.
     *
     * @param array<int, int|string> $parameters
     */
    public function validateMissingWith(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'missing_with');

        if (Arr::hasAny($this->data, $parameters)) {
            return $this->validateMissing($attribute, $value, $parameters);
        }

        return true;
    }

    /**
     * Validate that an attribute is missing when all given attributes are present.
     *
     * @param array<int, int|string> $parameters
     */
    public function validateMissingWithAll(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'missing_with_all');

        if (Arr::has($this->data, $parameters)) {
            return $this->validateMissing($attribute, $value, $parameters);
        }

        return true;
    }

    /**
     * Validate the value of an attribute is a multiple of a given value.
     *
     * @param array<int, int|string> $parameters
     */
    public function validateMultipleOf(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'multiple_of');

        if (! $this->validateNumeric($attribute, $value) || ! $this->validateNumeric($attribute, $parameters[0])) {
            return false;
        }

        try {
            $numerator = BigDecimal::of($this->trim($value));
            $denominator = BigDecimal::of($this->trim($parameters[0]));

            if ($numerator->isZero() && $denominator->isZero()) {
                return false;
            }

            if ($numerator->isZero()) {
                return true;
            }

            if ($denominator->isZero()) {
                return false;
            }

            return $numerator->remainder($denominator)->isZero();
        } catch (BrickMathException $e) {
            throw new BrickMathException('An error occurred while handling the multiple_of input values.', previous: $e);
        }
    }

    /**
     * Prepare the values and the other value for validation.
     *
     * @param array<int, int|string> $parameters
     */
    public function parseDependentRuleParameters(array $parameters): array
    {
        $other = Arr::get($this->data, $parameters[0]);

        $values = array_slice($parameters, 1);

        if ($this->shouldConvertToBoolean($parameters[0]) || is_bool($other)) {
            $values = $this->convertValuesToBoolean($values);
        }

        if (is_null($other)) {
            $values = $this->convertValuesToNull($values);
        }

        return [$values, $other];
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
     * Validate that other attributes do not exist when this attribute exists.
     */
    public function validateProhibits(string $attribute, mixed $value, mixed $parameters): bool
    {
        if ($this->validateRequired($attribute, $value)) {
            foreach ($parameters as $parameter) {
                if ($this->validateRequired($parameter, Arr::get($this->data, $parameter))) {
                    return false;
                }
            }
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
     * Indicate that an attribute is excluded.
     */
    public function validateExclude(): bool
    {
        return false;
    }

    /**
     * Indicate that an attribute should be excluded when another attribute has a given value.
     *
     * @param mixed $value
     */
    public function validateExcludeIf(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'exclude_if');

        if (! Arr::has($this->data, $parameters[0])) {
            return true;
        }

        [$values, $other] = $this->parseDependentRuleParameters($parameters);

        return ! in_array($other, $values, is_bool($other) || is_null($other));
    }

    /**
     * Indicate that an attribute should be excluded when another attribute does not have a given value.
     *
     * @param mixed $value
     */
    public function validateExcludeUnless(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'exclude_unless');

        [$values, $other] = $this->parseDependentRuleParameters($parameters);

        return in_array($other, $values, is_bool($other) || is_null($other));
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
     * Indicate that an attribute should be excluded when another attribute presents.
     *
     * @param mixed $value
     */
    public function validateExcludeWith(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'exclude_with');

        if (! Arr::has($this->data, $parameters[0])) {
            return true;
        }

        return false;
    }

    /**
     * Indicate that an attribute should be excluded when another attribute is missing.
     *
     * @param mixed $value
     */
    public function validateExcludeWithout(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'exclude_without');

        if ($this->anyFailingRequired($parameters)) {
            return false;
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

    public function validateDoesntStartWith(string $attribute, mixed $value, array $parameters): bool
    {
        return ! Str::startsWith($value, $parameters);
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

    public function validateDoesntEndWith($attribute, $value, $parameters): bool
    {
        return ! Str::endsWith($value, $parameters);
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
     */
    public function validateUrl(string $attribute, mixed $value, array $parameters = []): bool
    {
        return Str::isUrl($value, $parameters);
    }

    public function validateUlid(string $attribute, mixed $value): bool
    {
        return Str::isUlid($value);
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

    protected function convertValuesToNull(array $values): array
    {
        return array_map(function ($value) {
            return Str::lower($value) === 'null' ? null : $value;
        }, $values);
    }

    protected function shouldConvertToBoolean($parameter): bool
    {
        return in_array('boolean', Arr::get($this->rules, $parameter, []));
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

        return Arr::where(Arr::dot($attributeData), fn ($value, $key) => (bool) preg_match('#^' . $pattern . '\z#u', (string) $key));
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
        if (preg_match('/\[(.*)\]/', (string) $id, $matches)) {
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
            'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
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

    /**
     * Trim the value if it is a string.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function trim($value)
    {
        return is_string($value) ? trim($value) : $value;
    }
}
