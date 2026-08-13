# Validator

## Foreword

> [hyperf/validation](https://github.com/hyperf/validation) is derived from [illuminate/validation](https://github.com/illuminate/validation). We have made some modifications to it but kept the same validation rules. Here, we would like to thank the Laravel development team for implementing such a powerful and easy-to-use validator component.

## Installation

### Introduce Component Package

```bash
composer require hyperf/validation
```

### Add Middleware

You need to add a global middleware configuration of `Hyperf\Validation\Middleware\ValidationMiddleware` to the `config/autoload/middlewares.php` configuration file for the Server using the validator component. Below is an example of adding the corresponding global middleware to the `http` Server:

```php
<?php
return [
    // The http string below corresponds to the value of the name attribute of each server in config/autoload/server.php, meaning the corresponding middleware configuration only applies to that Server
    'http' => [
        // Configure your global middleware in the array, the order depends on the order of this array
        \Hyperf\Validation\Middleware\ValidationMiddleware::class
        // Other middleware hidden here
    ],
];
```

> If the global middleware is not set correctly, the usage of `FormRequest` may be invalid.

### Add Exception Handler

The exception handler mainly handles the `Hyperf\Validation\ValidationException` exception. We provide a `Hyperf\Validation\ValidationExceptionHandler` to handle it. You need to manually configure this exception handler into your project's `config/autoload/exceptions.php` file. Of course, you can also customize your exception handler.

```php
<?php
return [
    'handler' => [
        // Corresponds to your current Server name
        'http' => [
            \Hyperf\Validation\ValidationExceptionHandler::class,
        ],
    ],
];
```

### Publish Validator Language Files

Due to the existence of multi-language functionality, this component depends on the [hyperf/translation](https://github.com/hyperf/translation) component. If you have not added a configuration file for the Translation component, please execute the following command to publish the configuration file for the Translation component. If you have already published or manually added it, just publish the language files for the validator component:

Publish Translation component files:

```bash
php bin/hyperf.php vendor:publish hyperf/translation
```

Publish validator component files:

```bash
php bin/hyperf.php vendor:publish hyperf/validation
```

Executing the above command will publish the validator's language file `validation.php` to the corresponding language file directory, where `en` refers to the English language file and `zh_CN` refers to the Simplified Chinese language file. You can modify and customize the content of the `validation.php` file according to your actual needs.

```shell
/storage
    /languages
        /en
            validation.php
        /zh_CN
            validation.php

```

## Usage

### Form Request Validation

For complex validation scenarios, you can create a `FormRequest`. A Form Request is a custom request class that contains validation logic. You can create a form validation class named `FooRequest` by executing the following command:

```bash
php bin/hyperf.php gen:request FooRequest
```

The form validation class will be generated in the `app\Request` directory. If the directory does not exist, it will be automatically created when running the command.
Next, we add some validation rules to the `rules` method of that class:

```php
/**
 * Get the validation rules applied to the request
 */
public function rules(): array
{
    return [
        'foo' => 'required|max:255',
        'bar' => 'required',
    ];
}
```

So, how does the validation rule take effect? All you need to do is declare the request class as a parameter in the controller method using type hinting. This way, the incoming form request will be validated before the controller method is called, meaning you don't need to write any validation logic in the controller, which decouples these two parts of the code very well:

```php
<?php
namespace App\Controller;

use App\Request\FooRequest;

class IndexController
{
    public function index(FooRequest $request)
    {
        // Incoming request passed validation...
        
        // Get validated data...
        $validated = $request->validated();
    }
}
```

If validation fails, the validator will throw a `Hyperf\Validation\ValidationException` exception. You can handle this exception by adding a custom exception handling class. At the same time, we also provide a `Hyperf\Validation\ValidationExceptionHandler` exception handler to handle this exception, which you can also directly configure. However, the default exception handler may not meet your needs, so you can customize the behavior after validation failure by customizing the exception handler as needed.

#### Custom Error Messages

You can customize the error messages used by the form request by overriding the `messages` method, which should return an array of attribute/rule pairs and their corresponding error messages:

```php
/**
 * Get custom error messages for defined validation rules
 */
public function messages(): array
{
    return [
        'foo.required' => 'foo is required',
        'bar.required'  => 'bar is required',
    ];
}
```

#### Custom Validation Attributes

If you want to replace the `:attribute` part in the validation message with a custom attribute name, you can specify a custom name by overriding the `attributes` method. This method returns an array of attribute name and custom name key-value pairs:

```php
/**
 * Get custom attributes for validation errors
 */
public function attributes(): array
{
    return [
        'foo' => 'foo of request',
    ];
}
```

### Manually Creating Validators

If you do not want to use the automatic validation function of `FormRequest`, you can obtain the validator factory class by injecting the `ValidatorFactoryInterface` interface class, and then manually create a validator instance using the `make` method:

```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class IndexController
{
    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    public function foo(RequestInterface $request)
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'foo' => 'required',
                'bar' => 'required',
            ],
            [
                'foo.required' => 'foo is required',
                'bar.required' => 'bar is required',
            ]
        );

        if ($validator->fails()){
            // Handle exception
            $errorMessage = $validator->errors()->first();  
        }
        // Do something
    }
}
```

The first argument passed to the `make` method is the data that needs to be validated, and the second argument is the validation rule for that data.

#### Custom Error Messages

If necessary, you can also use custom error messages to replace the default values for validation. There are several ways to specify custom messages. First, you can pass the custom message as the third argument to the `make` method:

```php
<?php
$messages = [
    'required' => 'The :attribute field is required.',
];

$validator = $this->validationFactory->make($request->all(), $rules, $messages);
```

In this example, the `:attribute` placeholder will be replaced by the actual name of the validation field. In addition, you can use other placeholders in the validation message. For example:

```php
$messages = [
    'same'    => 'The :attribute and :other must match.',
    'size'    => 'The :attribute must be exactly :size.',
    'between' => 'The :attribute value :input is not between :min - :max.',
    'in'      => 'The :attribute must be one of the following types: :values',
];
```

#### Specify Custom Message for a Given Attribute

Sometimes you may only want to customize the error message for a specific field. Just use a "dot" after the attribute name to specify the validation rule:

```php
$messages = [
    'email.required' => 'We need to know your e-mail address!',
];
```

#### Specify Custom Messages in PHP Files

In most cases, you might specify custom messages in a file rather than passing them directly to the `Validator`. To do this, place your messages in the `custom` array within the `storage/languages/xx/validation.php` language file.

#### Specify Custom Attributes in PHP Files

If you wish to replace the `:attribute` part of the validation message with a custom attribute name, you can specify a custom name in the `attributes` array of the `storage/languages/xx/validation.php` language file:

```php
'attributes' => [
    'email' => 'email address',
],
```

### After Validation Hooks

The validator also allows you to add callback functions that are allowed after successful validation, so that you can perform further validation, or even add more error messages to the message collection. Simply use the `after` method on the validation instance:

```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class IndexController
{
    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    public function foo(RequestInterface $request)
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'foo' => 'required',
                'bar' => 'required',
            ],
            [
                'foo.required' => 'foo is required',
                'bar.required' => 'bar is required',
            ]
        );

        $validator->after(function ($validator) {
            if ($this->somethingElseIsInvalid()) {
                $validator->errors()->add('field', 'Something is wrong with this field!');
            }
        });
        
        if ($validator->fails()) {
            //
        }
    }
}
```

## Handling Error Messages

By calling the `errors` method through the `Validator` instance, a `Hyperf\Support\MessageBag` instance is returned, which has various convenient methods for handling error messages.

### View the First Error Message for a Specific Field

To view the first error message for a specific field, you can use the `first` method:

```php
$errors = $validator->errors();

echo $errors->first('foo');
```

### View All Error Messages for a Specific Field

If you need to get an array of all error messages for a specified field, you can use the `get` method:

```php
foreach ($errors->get('foo') as $message) {
    //
}
```

If you are validating an array field of a form, you can use `*` to get all error messages for each array element:

```php
foreach ($errors->get('foo.*') as $message) {
    //
}
```

### View All Error Messages for All Fields

If you want to get all error messages for all fields, you can use the `all` method:

```php
foreach ($errors->all() as $message) {
    //
}
```

### Determine If a Specific Field Has an Error Message

The `has` method can be used to determine whether an error message exists for a specified field:

```php
if ($errors->has('foo')) {
    //
}
```

### Scenes

The validator adds a scene function, which allows us to easily modify validation rules on demand.

> This functionality requires the version of this component to be greater than or equal to 2.2.7

Create a `SceneRequest` as follows:

```php
<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class SceneRequest extends FormRequest
{
    protected array $scenes = [
        'foo' => ['username'],
        'bar' => ['username', 'password'],
        'tar' => ['username' => 'string|required', 'password'],
    ];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'username' => 'required',
            'gender' => 'required',
        ];
    }
}
```

When we use it normally, all validation rules will be used, meaning `username` and `gender` are both mandatory.

We can set a scene to make this request only validate `username` as mandatory.

If we have configured `Hyperf\Validation\Middleware\ValidationMiddleware` and injected `SceneRequest` into the method,
it will cause the input parameter to be directly validated in the middleware, so the scene value will not take effect. Therefore, we need to obtain the corresponding `SceneRequest` from the container in the method and perform scene switching.

```php
<?php

namespace App\Controller;

use App\Request\DebugRequest;
use App\Request\SceneRequest;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController(prefix: 'foo')]
class FooController extends Controller
{
    public function scene()
    {
        $request = $this->container->get(SceneRequest::class);
        $request->scene('foo')->validateResolved();

        return $this->response->success($request->all());
    }
}
```

Of course, we can also switch scenes via the `Scene` annotation

```php
<?php

namespace App\Controller;

use App\Request\DebugRequest;
use App\Request\SceneRequest;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Validation\Annotation\Scene;

#[AutoController(prefix: 'foo')]
class FooController extends Controller
{
    #[Scene(scene:'bar1')]
    public function bar1(SceneRequest $request)
    {
        return $this->response->success($request->all());
    }

    #[Scene(scene:'bar2', argument: 'request')] // Bind to $request
    public function bar2(SceneRequest $request)
    {
        return $this->response->success($request->all());
    }

    #[Scene(scene:'bar3', argument: 'request')]
    #[Scene(scene:'bar3', argument: 'req')] // Support multiple parameters
    public function bar3(SceneRequest $request, DebugRequest $req)
    {
        return $this->response->success($request->all());
    }

    #[Scene()] // Default scene is method name, equivalent to #[Scene(scene: 'bar1')]
    public function bar1(SceneRequest $request)
    {
        return $this->response->success($request->all());
    }
}
```

## Validation Rules

Below is the list of valid rules and their functions:

##### accepted

The validated field must be `yes`, `on`, `1`, or `true`, which is useful when "agreeing to service agreements".

##### accepted_if:anotherfield,value,…
If another field being validated is equal to the specified value, the field being validated must be `yes`, `on`, `1`, or `true`, which is useful for validating "Terms of Service" acceptance or similar fields.

##### declined
The field being validated must be `no`, `off`, `0`, or `false`.

##### declined_if:anotherfield,value,…
If the value of another validation field is equal to the specified value, the value of the validation field must be `no`, `off`, `0`, or `false`.

##### active_url

The field being validated must be a valid value with `A` or `AAAA` records based on the PHP function `dns_get_record`.

##### after:date

The field being validated must be a value after the given date. The date will be passed through the PHP function strtotime:

```php
'start_date' => 'required|date|after:tomorrow'
```

You can specify another field to compare with the date instead of passing a date string to strtotime for execution:

```php
'finish_date' => 'required|date|after:start_date'
```

##### after_or_equal:date

The field being validated must be a value greater than or equal to the given date. For more information, please refer to the after:date rule.

##### alpha

The field being validated must be letters (including Chinese). To limit this validation rule to characters within the ASCII range (a-z and A-Z), you can provide the ascii option to the validation rule:

```php
'username' => 'alpha:ascii',
```

##### alpha_dash

The field being validated can contain letters (including Chinese) and numbers, as well as dashes and underscores. To limit this validation rule to characters within the ASCII range (a-z and A-Z), you can provide the ascii option to the validation rule:

```php
'username' => 'alpha_dash:ascii',
```

##### alpha_num

The field being validated must be letters (including Chinese) or numbers. To limit this validation rule to characters within the ASCII range (a-z and A-Z), you can provide the ascii option to the validation rule:

```php
'username' => 'alpha_num:ascii',
```

#### ascii

The field being validated must be entirely 7-bit ASCII characters.

##### array

The field being validated must be a PHP array.

##### required_array_keys:foo,bar,…

The field being validated must be an array and must contain at least the specified keys.

##### bail

Stop running other validation rules if the first validation rule fails.

##### before:date

Relative to after:date, the field being validated must be a value before the specified date. The date will be passed to the PHP strtotime function.

##### before_or_equal:date

The field being validated must be less than or equal to the given date. The date will be passed to the PHP strtotime function.

##### between:min,max

The size of the field being validated must be between the given minimum and maximum values. Strings, numbers, arrays, and files can all use this rule just like using the size rule:

'name' => 'required|between:1,20'

##### boolean

The field being validated must be convertible to a boolean value, receiving input such as true, false, 1, 0, "1", and "0".

##### boolean:strict

The field being validated must be convertible to a boolean value, only receiving true and false.

##### confirmed

The field being validated must have a matching field foo_confirmation. For example, if the validation field is password, you must input a matching password_confirmation field.

##### date

The field being validated must be a valid date based on the PHP strtotime function

##### date_equals:date

The field being validated must be equal to the given date. The date will be passed to the PHP strtotime function.

##### date_format:format

The field being validated must match the specified format. You can use the PHP function date or date_format to validate the field.

##### decimal:min,max

The field being validated must be of numerical type and must contain the specified number of decimal places:

```php
// Must have exactly two decimal places (e.g., 9.99)...
'price' => 'decimal:2'

// Must have 2 to 4 decimal places...
'price' => 'decimal:2,4'
```

##### lowercase

The field being validated must be lowercase.

##### uppercase

The field being validated must be uppercase.

##### mac_address

The field being validated must be a MAC address.

##### max_digits:value

The validated integer must have a maximum length of value.

##### min_digits:value

The validated integer must have at least value digits.

##### exclude

The current validated field will be excluded from the `validate` and `validated` methods.

##### exclude_if:anotherfield,value
If `anotherfield` equals `value`, the current validated field will be excluded from the `validate` and `validated` methods.

In some complex scenarios, you can also use the `Rule::excludeIf` method, which needs to return a boolean value or an anonymous function. If an anonymous function is returned, it should return `true` or `false` to decide whether the validated field should be excluded:

```php
use Hyperf\Validation\Rule;

$this->validationFactory->make($request->all(), [
    'role_id' => Rule::excludeIf($request->user()->is_admin),
]);

$this->validationFactory->make($request->all(), [
    'role_id' => Rule::excludeIf(fn () => $request->user()->is_admin),
]);
```

##### prohibited

The field requiring validation must not exist or be empty. If it meets any of the following conditions, the field is considered "empty":

1. Value is `null`.
2. Value is an empty string.
3. Value is an empty array or an empty Countable object.
4. Value is an uploaded file, but the file path is empty.

##### prohibited_if:anotherfield,value,…

If the `anotherfield` field equals any `value`, the field requiring validation must not exist or be empty. If it meets any of the following conditions, the field is considered "empty":

1. Value is `null`.
2. Value is an empty string.
3. Value is an empty array or an empty Countable object.
4. Value is an uploaded file, but the file path is empty.

If complex conditional prohibited logic is needed, the `Rule::prohibitedIf` method can be used. This method accepts a boolean value or a closure. When given a closure, the closure should return `true` or `false` to indicate whether the validation field should be prohibited:


```php
use Hyperf\Validation\Rule;

$this->validationFactory->make($request->all(), [
    'role_id' => Rule::prohibitedIf($request->user()->is_admin),
]);

$this->validationFactory->make($request->all(), [
    'role_id' => Rule::prohibitedIf(fn () => $request->user()->is_admin),
]);
```


##### missing

The field being validated must not exist in the input data.

##### missing_if:anotherfield,value,…

If the `anotherfield` field equals any `value`, then the field being validated must not exist.

##### missing_unless:anotherfield,value

The field being validated must not exist unless the `anotherfield` field equals any `value`.

##### missing_with:foo,bar,…

The field being validated must not exist if any other specified field exists.

##### missing_with_all:foo,bar,…

The field being validated must not exist if all other specified fields exist.

##### multiple_of:value

The field being validated must be a multiple of `value`.

##### doesnt_start_with:foo,bar,…

The field being validated cannot start with any of the given values.

##### doesnt_end_with:foo,bar,…

The field being validated cannot end with any of the given values.

##### different:field

The field being validated must be a value different from the specified field.

##### digits:value

The field being validated must be a number and have the length specified by `value`.

##### digits_between:min,max

The numerical length of the field being validated must be between the minimum and maximum values.

##### dimensions

The dimensions of the validated image must satisfy the constraints specified by this rule's parameters:

```php
'avatar' => 'dimensions:min_width=100,min_height=200'
```

Valid constraints include: `min_width`, `max_width`, `min_height`, `max_height`, `width`, `height`, `ratio`.

The `ratio` constraint restricts the width/height ratio, which can be expressed by an expression like `3/2` or a floating-point number `1.5`:

```php
'avatar' => 'dimensions:ratio=3/2'
```

Since this rule requires multiple parameters, you can use the `Rule::dimensions` method to construct this rule:

```php
use Hyperf\Validation\Rule;

public function rules(): array
{
return [
           'avatar' => [
              'required',
              Rule::dimensions()->maxWidth(1000)->maxHeight(500)->ratio(3 / 2),
           ],
       ];
}
```
##### distinct

When processing arrays, the field being validated cannot contain duplicate values:

```php
'foo.*.id' => 'distinct'
```

##### email

The field being validated must be a correctly formatted email address.

##### exists:table,column

The field being validated must exist in the specified data table.

Basic usage:

```php
'state' => 'exists:states'
```

If the `column` option is not specified, the field name will be used.

Specify custom column name:

```php
'state' => 'exists:states,abbreviation'
```

Sometimes, you may need to specify the database connection to be used for the `exists` query, which can be achieved by prefixing the table name with the database connection followed by a ".", or by specifying the model class name for automatic resolution:

```php
// Prefix database connection approach
'email' => 'exists:connection.staff,email'

// Model class name automatic resolution approach
'email' => 'exists:StaffModel::class,email'
```

If you want to customize the query executed by the validation rule, you can use the `Rule` class to define the rule. In this example, we also specified validation rules in the form of an array instead of using the `|` character to delimit them:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($data, [
    'email' => [
        'required',
        Rule::exists('staff')->where(function ($query) {
            $query->where('account_id', 1);
        }),
    ],
]);
```

##### file

The field being validated must be a successfully uploaded file.

##### filled

The field being validated must not be empty if it exists.

##### gt:field

The field being validated must be greater than the given `field` field. These two fields must be of the same type and it is applicable to strings, numbers, arrays, and files, similar to the `size` rule.

##### gte:field

The field being validated must be greater than or equal to the given `field` field. These two fields must be of the same type and it is applicable to strings, numbers, arrays, and files, similar to the `size` rule.

##### image

The file being validated must be an image (`jpeg`, `png`, `bmp`, `gif`, or `svg`).

##### in:foo,bar…

The field being validated must be within the given list. Since this rule often requires us to `implode` an array, we can use `Rule::in` to construct this rule:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($data, [
    'zones' => [
        'required',
        Rule::in(['first-zone', 'second-zone']),
    ],
]);
```

##### in_array:anotherfield

The field being validated must exist in another field value.

##### integer

The field being validated must be an integer (both String and Integer types can pass validation).

##### integer:strict

The field being validated must be an integer (only Integer type can pass validation).

##### ip

The field being validated must be an IP address.

##### ipv4

The field being validated must be an IPv4 address.

##### ipv6

The field being validated must be an IPv6 address.

##### json

The field being validated must be a valid JSON string.

##### lt:field

The field being validated must be less than the given `field` field. These two fields must be of the same type and it is applicable to strings, numbers, arrays, and files, similar to the `size` rule.

##### lte:field

The field being validated must be less than or equal to the given `field` field. These two fields must be of the same type and it is applicable to strings, numbers, arrays, and files, similar to the `size` rule.

##### max:value

The field being validated must be less than or equal to the maximum value, and the usage is the same as the `size` rule for string, numeric, array, and file fields.

##### mimetypes：text/plain…

The file being validated must match one of the given `MIME` file types:

```php
'video' => 'mimetypes:video/avi,video/mpeg,video/quicktime'
```

To determine the `MIME` type of the uploaded file, the component will read the file content to guess the `MIME` type, which may be different from the client-side `MIME` type.

##### mimes:foo,bar,…

The `MIME` type of the file being validated must be one of the extension types listed by this rule.
Basic usage of `MIME` rule:

```php
'photo' => 'mimes:jpeg,bmp,png'
```

Although you only specified the extension, this rule actually validates the file `MIME` type obtained by reading the file content.
A complete list of `MIME` types and their corresponding extensions can be found here: [mime types](http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types)

##### min:value

Relative to `max:value`, the field being validated must be greater than or equal to the minimum value. For string, numeric, array, and file fields, the usage is consistent with the `size` rule.

##### not_in:foo,bar,…

The field being validated cannot be in the given list. Similar to the `in` rule, we can use the `Rule::notIn` method to construct the rule:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($data, [
    'toppings' => [
        'required',
        Rule::notIn(['sprinkles', 'cherries']),
    ],
]);
```

##### not_regex:pattern

The field being validated cannot match the given regular expression.

Note: When using `regex/not_regex` patterns, the rule must be placed in an array and cannot use a pipe delimiter, especially when the regular expression contains a pipe symbol.

##### nullable

The field being validated can be `null`, which is useful when validating some raw data that can be `null`, such as integer or string.

##### numeric

The field being validated must be numeric.

##### present

The field being validated must appear in the input data but can be empty.

##### regex:pattern

The field being validated must match the given regular expression.
This rule uses the `PHP` `preg_match` function under the hood. Therefore, the specified pattern needs to follow the format required by the `preg_match` function and contain valid delimiters. For example:

```php
 'email' => 'regex:/^.+@.+$/i'
```

Note: When using `regex/not_regex` patterns, the rule must be placed in an array and cannot use a pipe delimiter, especially when the regular expression contains a pipe symbol.

##### required

The field being validated cannot be empty. In the following cases, the field value is empty:
- Value is `null`
- Value is an empty string
- Value is an empty array or empty `Countable` object
- Value is an uploaded file but the path is empty

##### required_if:anotherfield,value,…

The field being validated must exist and cannot be empty when `anotherfield` equals the specified value `value`.
If you want to construct more complex conditions for the `required_if` rule, you can use the `Rule::requiredIf` method, which receives a boolean value or a closure. When passing a closure, it returns `true` or `false` to indicate whether the validated field is mandatory:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($request->all(), [
    'role_id' => Rule::requiredIf($request->user()->is_admin),
]);

$validator = $this->validationFactory->make($request->all(), [
    'role_id' => Rule::requiredIf(function () use ($request) {
        return $request->user()->is_admin;
    }),
]);
```

##### required_unless:anotherfield,value,…

Unless the `anotherfield` field equals `value`, the field being validated cannot be empty.

##### required_with:foo,bar,…

The field being validated is mandatory only if any of the other specified fields exist.

##### required_with_all:foo,bar,…

The field being validated is mandatory only if all specified fields exist.

##### required_without:foo,bar,…

The field being validated is mandatory only if any of the specified fields do not exist.

##### required_without_all:foo,bar,…

The field being validated is mandatory only if all specified fields do not exist.

##### same:field

The given field and the validated field must match.

##### size:value

The field being validated must have a size/size that matches the given value `value`. For strings, `value` is the corresponding number of characters; for numbers, `value` is the given integer value; for arrays, `value` is the length of the array; for files, `value` is the corresponding file size in kilobytes (KB).

##### starts_with:foo,bar,...

The field being validated must start with a given value.

##### string

The field being validated must be a string. If the field is allowed to be empty, you need to assign the `nullable` rule to that field.

##### timezone

The field being validated must be a valid timezone identifier based on the `PHP` function `timezone_identifiers_list`.

##### unique:table,column,except,idColumn

The field being validated must be unique in the given data table. If the `column` option is not specified, the field name will be used as the default `column`.

1. Specify custom column name:

```php
'email' => 'unique:users,email_address'
```

2. Custom database connection:
   Sometimes, you may need to customize the database connection generated by the validator, as seen above, setting `unique:users` as the validation rule will use the default database connection to query the database. To override the default connection, specify the connection after the table name with a ".", or automatically resolve it by specifying the model class name:

```php
// Prefix database connection approach
'email' => 'unique:connection.users,email_address'

// Model class name automatic resolution approach
'email' => 'unique:UserModel::class,email_address'
```

3. Force a unique rule that ignores a given `ID`:
   Sometimes, you may want to ignore a given `ID` when checking for uniqueness. For example, consider an "Update Attribute" interface that includes username, email address, and location. You want to validate that the email address is unique. However, if the user only changes the username field and does not change the email field, you don't want to throw a validation error because the user already owns that email address. You only want to throw a validation error if the email provided by the user has already been used by someone else.

   To tell the validator to ignore a user `ID`, you can use the `Rule` class to define this rule. We should also specify the validation rules in the form of an array, instead of using `|` to delimit the rules:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($data, [
    'email' => [
        'required',
        Rule::unique('users')->ignore($user->id),
    ],
]);
```

In addition to passing the model instance primary key value to the `ignore` method, you can also pass the entire model instance. The component will automatically resolve the primary key value from the model instance:

```php
Rule::unique('users')->ignore($user)
```

If the primary key field used by your data table is not `id`, you can specify the field name when calling the `ignore` method:

```php
'email' => Rule::unique('users')->ignore($user->id, 'user_id')
```

By default, the `unique` rule checks for uniqueness in the column that matches the attribute name to be validated. However, you can specify a different column name as the second argument of the `unique` method:

```php
Rule::unique('users', 'email_address')->ignore($user->id),
```

4. Add additional `where` clauses:

When using the `where` method to customize a query, you can also specify additional query constraints. For example, we add a constraint to validate that `account_id` is 1:

```php
'email' => Rule::unique('users')->where(function ($query) {
    $query->where('account_id', 1);
})
```

##### url

The field being validated must be a valid URL.

##### uuid

The field being validated must be a valid RFC 4122 (version 1, 3, 4, or 5) globally unique identifier (UUID).

##### sometimes

Add conditional rules
Validate when present

In some scenarios, you might want to perform validation checks only if a certain field exists. To quickly implement this, add the `sometimes` rule to the rule list:

```php
$validator = $this->validationFactory->make($data, [
    'email' => 'sometimes|required|email',
]);
```

In the example above, the `email` field will only be validated if it exists in the `$data` array.

Note: If you are trying to validate a field that always exists but might be empty, refer to the optional field notes.

Complex conditional validation

Sometimes you might want to add validation rules based on more complex conditional logic. For example, you might want a given field to be required only if another field value is greater than 100, or you might need both fields to have given values only when another field exists. Adding this validation rule is not a headache. First, create a static rule that will never change in the `Validator` instance:

```php
$validator = $this->validationFactory->make($data, [
    'email' => 'required|email',
    'games' => 'required|numeric',
]);
```

Let's assume our Web application serves game collectors. If a game collector registers for our application and owns more than 100 games, we want them to explain why they have so many games, for example, perhaps they are running a used game store, or maybe they just like to collect. To add this condition, we can use the `sometimes` method on the `Validator` instance:

```php
$v->sometimes('reason', 'required|max:500', function($input) {
    return $input->games >= 100;
});
```

The first argument passed to the `sometimes` method is the name field we need conditional validation for, the second argument is the rule we want to add, and the rule is added if the closure passed as the third argument returns `true`. This method makes building complex conditional validation simple, and you can even add conditional validation for multiple fields at once:

```php
$v->sometimes(['reason', 'cost'], 'required', function($input) {
    return $input->games >= 100;
});
```

Note: The `$input` parameter passed to the closure is an instance of `Hyperf\Support\Fluent`, which can be used to access inputs and files.

### Validating Array Input

Validating form array input fields is no longer a painful thing. For example, if the incoming HTTP request contains the `photos[profile]` field, you can validate it like this:

```php
$validator = $this->validationFactory->make($request->all(), [
    'photos.profile' => 'required|image',
]);
```

We can also validate each element of an array. For example, to validate if each email in the given array input is unique, you can do this (this is for cases where submitted array fields are two-dimensional arrays, such as `person[][email]` or `person[test][email]`):

```php
$validator = $this->validationFactory->make($request->all(), [
    'person.*.email' => 'email|unique:users',
    'person.*.first_name' => 'required_with:person.*.last_name',
]);
```

Similarly, you can also use the `*` character in language files to specify validation messages, thus allowing you to use a single validation message definition for validation rules based on array fields:

```php
'custom' => [
    'person.*.email' => [
        'unique' => 'The email address of each person must be unique',
    ]
],
```

### Custom Validation Rules

#### Register Custom Validation Rules

The `Validation` component uses an event mechanism to implement custom validation rules. We have defined the `ValidatorFactoryResolved` event. What you need to do is define a listener for `ValidatorFactoryResolved` and implement validator registration in the listener. An example is as follows:

```php
namespace App\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Event\ValidatorFactoryResolved;
use Hyperf\Validation\Validator;

#[Listener]
class ValidatorFactoryResolvedListener implements ListenerInterface
{

    public function listen(): array
    {
        return [
            ValidatorFactoryResolved::class,
        ];
    }

    public function process(object $event): void
    {
        /**  @var ValidatorFactoryInterface $validatorFactory */
        $validatorFactory = $event->validatorFactory;
        // Registered the foo validator
        $validatorFactory->extend('foo', function (string $attribute, mixed $value, array $parameters, Validator $validator): bool {
            return $value == 'foo';
        });
        // When creating a custom validation rule, you might sometimes need to define a custom placeholder for the error message, here the :foo placeholder is extended
        $validatorFactory->replacer('foo', function (string $message, string $attribute, string $rule, array $parameters): array|string {
            return str_replace(':foo', $attribute, $message);
        });
    }
}
```

#### Custom Error Message

You also need to define an error message for your custom rule. You can achieve this by using an inline custom message array or by adding an entry in the validation language file. The message should be placed in the first dimension of the array, rather than within the custom array used only for storing attribute-specific error messages. Taking the `foo` custom validator from the previous section as an example:

Add the following content to the array in `storage/languages/en/validation.php`:

```php
    'foo' => 'The :attribute must be foo',
```

Add the following content to the array in `storage/languages/zh_CN/validation.php`:

```php    
    'foo' => ' :attribute must be foo',
```

#### Custom Validator Usage

```php
<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class DemoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Use the foo validator
            'name' => 'foo'
        ];
    }
}
```
