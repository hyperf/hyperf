# Annotation

Annotation is a very powerful feature of Hyperf that can be used to reduce a lot of configuration in the form of annotation and to implement many very convenient features.

## Concept

### What is annotation and what is comment?

Before interpreting the annotation, we need to define the difference between `annotation` and `comment`:
- Comment: For the programmer to see, help understand the code, explain and explain the code.
- Annotation: For the system or application to see, the definition of metadata. There is no effect when used alone, it needs to be used in conjunction with the application to use its metadata to works.

### How it parse ?

Hyperf uses the [doctrine/annotations](https://github.com/doctrine/annotations) package to parse the annotations in the code. The annotations must be written in the standard comment block below to be parsed correctly. Other formats cannot correctly parsed.
Example:
```php
/**
 * @AnnotationClass()
 */
```
The syntax of writing `@AnnotationClass()` in the standard comment block indicates that the object (class, class method, class attribute) of the current comment block is annotated, and `AnnotationClass` corresponds to an `annotation class`. The class name of the class can be written in the namespace of the whole class, or just the class name, but the annotation class needs to be in the current class `use` to ensure that the correct annotation class can be found according to the namespace.

### How is works ?

We have said that annotations are just metadata definitions that need to work with the application to work. In Hyperf, the data in the annotations are collected into the `Hyperf\Di\Annotation\AnnotationCollector` class for usage of application, depending on your demand, you can also collect the data to your custom classes and then read and utilize the collected annotation metadata in the place where the annotations themselves are expected to work to achieve the desired functional implementation.

### Ignore some annotations

In some cases we may wish to ignore certain annotations. For example, when we access some tools that automatically generate documents, many tools use annotations to define the relevant structural content of the document. These annotations may not be in line with how Hyperf is used, we can set the concern to be ignored by `config/autoload/annotations.php`.

```php
return [
    'scan' => [
        // Annotations in the ignore_annotations array will be ignored by the annotation scanner
        'ignore_annotations' => [
            'mixin',
        ],
    ],
];
```

## Usage of Annotation

There are three apply types of annotation, `class`, `method of class` and `property of class`.

### Use class level annotation

Class level annotation definitions are in the comment block above the `class` keyword. For example, the commonly used `@Controller` and `@AutoController` are examples of the use of class level annotation. The following code example is an example of correctly using class level annotation, indicating The `@ClassAnnotation` annotation is applied to the `Foo` class.
 
```php
/**
 * @ClassAnnotation()
 */
class Foo {}
```

### Use method level annotation

Method level annotation definitions are in the comment block above the class method. For example, the commonly used `@RequestMapping` is example of the use of method level annotation. The following code example is an example of correctly using method level annotation, indicating The `@MethodAnnotation` annotation is applied to the `bar` method of `Foo` class.

```php
class Foo
{
    /**
     * @MethodAnnotation()
     */
    public function bar()
    {
        // some code
    }
}
```

### Use property level of annotation

Property level annotation definitions are in the comment block above the property. For example, the commonly used `@Value` and `@Inject` are examples of the use of property level annotation. The following code example is an example of correctly using property level annotation, indicating The `@PropertyAnnotation` annotation is applied to the `$bar` property of `Foo` class.
  
```php
class Foo
{
    /**
     * @PropertyAnnotation()
     */
    private $bar;
}
```

### The passing of annotation parameter

- Pass the main single parameter `@DemoAnnotation("value")`
- Pass the string parameter `@DemoAnnotation(key1="value1", key2="value2")`
- Pass the array parameter `@DemoAnnotation(key={"value1", "value2"})`

## Custom Annotation

### Create a Annotation class

Create an annotation class into anywhere, as in the following code example:

```php
namespace App\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
class Bar extends AbstractAnnotation
{
    // some code
}

/**
 * @Annotation
 * @Target("CLASS")
 */
class Foo extends AbstractAnnotation
{
    // some code
}
```

> Notice that `@Annotation` and `@Target` annotations in Annotation Class are Global Annotation，no need fo `use` keyword to define the namespace.

`@Target` has the following parameters:
- `METHOD` annotation means allows define on class methods
- `PROPERTY` annotation means allows define on class properties
- `CLASS` annotation means allows define on class
- `ALL` annotation means allows define in all scopes

Let's note that in the example code above, the annotation class inherits the `Hyperf\Di\Annotation\AbstractAnnotation` abstract class. This is not required for annotation classes, but for Hyperf annotation classes, inherit `Hyperf\Di\Annotation\AnnotationInterface` interface is required, so the role of the abstract class here is to provide a minimal definition. The abstract class has been implemented for you to `automatically assign annotation parameters to class properties`, `automatically collects the annotation data to AnnotationCollector`.

### Custom Annotation Collector

The specific execution flow of the collection of annotation is also implemented in the annotation class. The related method is constrained by `Hyperf\Di\Annotation\AnnotationInterface`. The interface requires the implementation of the following three methods, you can according to your own needs to implement the corresponding logic:

- `public function collectClass(string $className): void;` This method will be fired when the annotation is defined in the class
- `public function collectMethod(string $className, ?string $target): void;` This method will be fired when the annotation is defined in the method
- `public function collectProperty(string $className, ?string $target): void` This method will be fired when the annotation is defined in the property

### Usage of annotation data

When there is no custom annotation collection method, the annotation metadata will be collected in the `Hyperf\Di\Annotation\AnnotationCollector` class by default. The static method of the class can easily obtain the corresponding metadata for logical judgment or achieve.

## IDE Plugin of Annotation

Because `PHP` does not natively support `annotation`, `IDE` does not add annotation feature support by default. But we can add third-party plugins to let `IDE` support `annotation` feature.

### PhpStorm

We can search for `PHP Annotations` in `Plugins` and find the corresponding component [PHP Annotations](https://github.com/Haehnchen/idea-php-annotation-plugin). Then install the plugin, restart `PhpStorm`, you can use the annotation feature happily. It mainly provides the features of adding automatic jump and code reminder support for annotation classes, and automatically referencing the corresponding namespace when annotations are used.
