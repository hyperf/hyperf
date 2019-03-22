# 注解

注解是 Hyperf 非常强大的一项功能，可以通过注解的形式减少很多的配置，以及实现很多非常方便的功能。

## 概念

### 什么是注解什么是注释？

在解释注解之前我们需要先定义一下 `注解` 与 `注释` 的区别：   
- 注释：给程序员看，帮助理解代码，对代码起到解释、说明的作用。
- 注解：给应用程序看，用于元数据的定义，单独使用时没有任何作用，需配合应用程序对其元数据进行利用才有作用。

### 注解解析如何实现？

Hyperf 使用了 [doctrine/annotations](https://github.com/doctrine/annotations) 包来对代码内的注解进行解析，注解必须写在下面示例的标准注释块才能被正确解析，其它格式均不能被正确解析。
注释块示例：
```php
/**
 * @AnnotationClass()
 */
```
在标准注释块内通过书写 `@AnnotationClass()` 这样的语法即表明对当前注释块所在位置的对象(类、类方法、类属性)进行了注解的定义， `AnnotationClass` 对应的是一个 `注解类` 的类名，可写全类的命名空间，亦可只写类名，但需要在当前类 `use` 该注解类以确保能够根据命名空间找到正确的注解类。

### 注解是如何发挥作用的？

我们有说到注解只是元数据的定义，需配合应用程序才能发挥作用，在 Hyperf 里，注解内的数据会被收集到 `Hyperf\Di\Annotation\AnnotationCollector` 类供应用程序使用，当然根据您的实际情况，也可以收集到您自定义的类去，随后在这些注解本身希望发挥作用的地方对已收集的注解元数据进行读取和利用，以达到期望的功能实现。

### 忽略某些注解

在一些情况下我们可能希望忽略某些 注解，比如我们在接入一些自动生成文档的工具时，有不少工具都是通过注解的形式去定义文档的相关结构内容的，而这些注解可能并不符合 Hyperf 的使用方式，我们可以通过在 `config/autoload/annotations.php` 内将相关注解设置为忽略。

```php
return [
    'scan' => [
        // ignore_annotations 数组内的注解都会被注解扫描器忽略
        'ignore_annotations' => [
            'mixin',
        ],
    ],
];
```


## 使用注解

注解一共有3种应用对象，分别是 `类`、`类方法` 和 `类属性`。

### 使用类注解

类注解定义是在 `class` 关键词上方的注释块内，比如常用的 `@Controller` 和 `@AutoController` 就是类注解的使用典范，下面的代码示例则为一个正确使用类注解的示例，表明 `@ClassAnnotation` 注解应用于 `Foo` 类。   
```php
/**
 * @ClassAnnotation()
 */
class Foo {}
```

### 使用类方法注解

类方法注解定义是在方法上方的注释块内，比如常用的 `@RequestMapping` 就是类方法注解的使用典范，下面的代码示例则为一个正确使用类方法注解的示例，表明 `@MethodAnnotation` 注解应用于 `Foo::bar()` 方法。   
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

### 使用类属性注解

类属性注解定义是在属性上方的注释块内，比如常用的 `@Value` 和 `@Inject` 就是类属性注解的使用典范，下面的代码示例则为一个正确使用类属性注解的示例，表明 `@MethodAnnotation` 注解应用于 `Foo::bar()` 方法。   
```php
class Foo
{
    /**
     * @PropertyAnnotation()
     */
    private $bar;
}
```

### 注解参数传递

- 传递主要的单个参数 `@DemoAnnotation("value")`
- 传递字符串参数 `@DemoAnnotation(key1="value1", key2="value2")`
- 传递数组参数 `@DemoAnnotation(key={"value1", "value2"})`

## 自定义注解

### 创建一个注解类

在任意地方创建注解类，如下代码示例：    

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

> 注意注解类的 `@Annotation` 和 `@Target` 注解为全局注解，无需 `use` 

其中 `@Target` 有如下参数：   
- `METHOD` 注解允许定义在类方法上
- `PROPERTY` 注解允许定义在类属性上
- `CLASS` 注解允许定义在类上
- `ALL` 注解允许定义在任何地方

我们注意一下在上面的示例代码中，注解类都继承了 `Hyperf\Di\Annotation\AbstractAnnotation` 抽象类，对于注解类来说，这个不是必须的，但对于 Hyperf 的注解类来说，继承 `Hyperf\Di\Annotation\AnnotationInterface` 接口类是必须的，那么抽象类在这里的作用是提供极简的定义方式，该抽象类已经为您实现了`注解参数自动分配到类属性`、`根据注解使用位置自动按照规则收集到 AnnotationCollector` 这样非常便捷的功能。

### 自定义注解收集器

注解的收集时具体的执行流程也是在注解类内实现的，相关的方法由 `Hyperf\Di\Annotation\AnnotationInterface` 约束着，该接口类要求了下面 3 个方法的实现，您可以根据自己的需求实现对应的逻辑：

- `public function collectClass(string $className): void;` 当注解定义在类时被扫描时会触发该方法
- `public function collectMethod(string $className, ?string $target): void;` 当注解定义在类方法时被扫描时会触发该方法
- `public function collectProperty(string $className, ?string $target): void` 当注解定义在类属性时被扫描时会触发该方法

### 利用注解数据

在没有自定义注解收集方法时，默认会将注解的元数据统一收集在 `Hyperf\Di\Annotation\AnnotationCollector` 类内，通过该类的静态方法可以方便的获取对应的元数据用于逻辑判断或实现。