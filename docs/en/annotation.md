# Annotation

Annotation is a very powerful feature of Hyperf that can be used to reduce a lot of configuration in the form of annotation and to implement many very convenient features.

## Concept

### What is annotation?

Attributes offer the ability to add structured, machine-readable metadata information on declarations in code: Classes, methods, functions, parameters, properties and class constants can be the target of an attribute. The metadata defined by attributes can then be inspected at runtime using the Reflection APIs. Attributes could therefore be thought of as a configuration language embedded directly into code.

With attributes the generic implementation of a feature and its concrete use in an application can be decoupled. In a way it is comparable to interfaces and their implementations. But where interfaces and implementations are about code, attributes are about annotating extra information and configuration. Interfaces can be implemented by classes, yet attributes can also be declared on methods, functions, parameters, properties and class constants. As such they are more flexible than interfaces.

A simple example of attribute usage is to convert an interface that has optional methods to use attributes. Lets assume an ActionHandler interface representing an operation in an application, where some implementations of an action handler require setup and others do not. Instead of requiring all classes that implement ActionHandler to implement a method setUp(), an attribute can be used. One benefit of this approach is that we can use the attribute several times.

### How is works ?

We have said that annotations are just metadata definitions that need to work with the application to work. In Hyperf, the data in the annotations are collected into the `Hyperf\Di\Annotation\AnnotationCollector` class for usage of application, depending on your demand, you can also collect the data to your custom classes and then read and utilize the collected annotation metadata in the place where the annotations themselves are expected to work to achieve the desired functional implementation.

### Ignore some annotations

In some cases we may wish to ignore certain annotations. For example, when we access some tools that automatically generate documents, many tools use annotations to define the relevant structural content of the document. These annotations may not be in line with how Hyperf is used, we can set the concern to be ignored by `config/autoload/annotations.php`.

```php
use JetBrains\PhpStorm\ArrayShape;

return [
    'scan' => [
        // Annotations in the ignore_annotations array will be ignored by the annotation scanner
        'ignore_annotations' => [
            ArrayShape::class,
        ],
    ],
];
```

## Usage of Annotation

There are three apply types of annotation, `class`, `method of class` and `property of class`.

### Use class level annotation

Class level annotation definitions are in the comment block above the `class` keyword. For example, the commonly used `Controller` and `AutoController` are examples of the use of class level annotation. The following code example is an example of correctly using class level annotation, indicating The `ClassAnnotation` annotation is applied to the `Foo` class.

```php
<?php
#[ClassAnnotation]
class Foo {}
```

### Use method level annotation

Method level annotation definitions are in the comment block above the class method. For example, the commonly used `RequestMapping` is example of the use of method level annotation. The following code example is an example of correctly using method level annotation, indicating The `MethodAnnotation` annotation is applied to the `bar` method of `Foo` class.

```php
<?php
class Foo
{
    #[MethodAnnotation]
    public function bar()
    {
        // some code
    }
}
```

### Use property level of annotation

Property level annotation definitions are in the comment block above the property. For example, the commonly used `Value` and `Inject` are examples of the use of property level annotation. The following code example is an example of correctly using property level annotation, indicating The `PropertyAnnotation` annotation is applied to the `$bar` property of `Foo` class.

```php
<?php
class Foo
{
    #[PropertyAnnotation]
    private $bar;
}
```

### The passing of annotation parameter

- Pass the main single parameter `#[DemoAnnotation('value')]`
- Pass the string parameter `#[DemoAnnotation(key1: 'value1', key2: 'value2')]`
- Pass the array parameter `#[DemoAnnotation(key: ['value1', 'value2'])]`

## Custom Annotation

### Create an Annotation class

Create an annotation class into anywhere, as in the following code example:

```php
<?php
namespace App\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Bar extends AbstractAnnotation
{
    // some code
}

#[Attribute(Attribute::TARGET_CLASS)]
class Foo extends AbstractAnnotation
{
    // some code
}
```

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
