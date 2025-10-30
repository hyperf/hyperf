# Hyperf Copilot Instructions

## Project Overview

Hyperf is a high-performance PHP CLI framework built on top of Swoole/Swow coroutines. It's designed for building microservices and middleware with extreme performance and flexibility. The framework follows a modular architecture with 100+ separate components that can be used independently.

### Key Features
- **Coroutine-based**: Built on Swoole/Swow for non-blocking I/O operations
- **Modular Design**: Each feature is a separate Composer package
- **PSR Standards**: Strict adherence to PSR standards for interoperability
- **AOP & DI**: Advanced Aspect-Oriented Programming and Dependency Injection
- **High Performance**: Handles massive traffic with minimal resource usage

## Architecture & Structure

### Repository Structure
```
hyperf/
├── src/                    # All framework components (100+ packages)
│   ├── framework/         # Core framework functionality
│   ├── di/               # Dependency Injection container
│   ├── config/           # Configuration management
│   ├── database/         # Database ORM (Eloquent-based)
│   ├── http-server/      # HTTP server implementation
│   ├── json-rpc/         # JSON-RPC client/server
│   ├── grpc/             # gRPC support
│   └── ...               # Many other components
├── docs/                 # Multi-language documentation
├── bin/                  # Build scripts and utilities
└── bootstrap.php         # Framework bootstrap
```

### Component Design Principles
1. **PSR Compliance**: All components follow PSR standards first
2. **Framework Agnostic**: Components work outside Hyperf context
3. **Constructor Injection**: Only use constructor-based DI, no annotations for DI
4. **Interface-based**: Depend on interfaces, not implementations
5. **Hyperf Enhancements**: Framework-specific features as separate enhancement classes

## Coding Standards

### PHP Standards
- **PHP Version**: Minimum PHP 8.1+
- **Strict Types**: Always use `declare(strict_types=1);`
- **PSR-2/PSR-12**: Code style enforced by PHP-CS-Fixer
- **PHPStan Level 6**: Static analysis for type safety

### File Header Template
```php
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
```

### Code Style Rules
- **Array Syntax**: Use short array syntax `[]`
- **Concatenation**: Single space around concatenation operator ` . `
- **Method Chaining**: Break long chains across multiple lines
- **Import Statements**: Group and sort use statements
- **Naming**: Use descriptive names, PascalCase for classes, camelCase for methods

## Component Development

### Creating New Components
1. **Directory Structure**: Follow standard component structure
   ```
   src/component-name/
   ├── src/              # Source code
   ├── tests/            # PHPUnit tests
   ├── composer.json     # Package definition
   ├── LICENSE           # MIT license
   └── .gitattributes    # Git attributes
   ```

2. **composer.json Template**:
   ```json
   {
       "name": "hyperf/component-name",
       "description": "Component description",
       "license": "MIT",
       "require": {
           "php": ">=8.1"
       },
       "autoload": {
           "psr-4": {
               "Hyperf\\ComponentName\\": "src/"
           }
       },
       "extra": {
           "hyperf": {
               "config": "Hyperf\\ComponentName\\ConfigProvider"
           }
       }
   }
   ```

3. **ConfigProvider**: Every component needs a ConfigProvider
   ```php
   class ConfigProvider
   {
       public function __invoke(): array
       {
           return [
               'dependencies' => [],
               'annotations' => [
                   'scan' => [
                       'paths' => [__DIR__],
                   ],
               ],
               'commands' => [],
               'listeners' => [],
               'publish' => [],
           ];
       }
   }
   ```

### Dependency Management
- **Core Dependencies**: Minimal, only essential PSR interfaces
- **Framework Dependencies**: Use `suggest` section, not `require`
- **Version Constraints**: Use tilde `~3.1.0` for Hyperf packages
- **Optional Features**: Make heavy dependencies optional with graceful degradation

## Common Patterns

### Service Providers & Factories
```php
// Factory pattern for complex object creation
class SomeServiceFactory
{
    public function __invoke(ContainerInterface $container): SomeService
    {
        $config = $container->get(ConfigInterface::class);
        return new SomeService($config->get('some.config'));
    }
}
```

### Event-Driven Architecture
```php
// Event classes
class SomeEvent
{
    public function __construct(public readonly string $data) {}
}

// Listener classes
#[Listener]
class SomeListener implements ListenerInterface
{
    public function listen(): array
    {
        return [SomeEvent::class];
    }

    public function process(object $event): void
    {
        // Handle event
    }
}
```

### Annotation/Attribute Usage
```php
// Use PHP 8 attributes for metadata
#[Controller(prefix: '/api')]
class ApiController
{
    #[GetMapping(path: '/users')]
    public function users(): array
    {
        return [];
    }
}
```

### Coroutine Patterns
```php
// Coroutine-safe operations
use Hyperf\Context\Context;
use Hyperf\Coroutine\Coroutine;

// Context passing
Context::set('key', $value);
$value = Context::get('key');

// Concurrent execution
$results = Coroutine::parallel([
    fn() => $this->serviceA->getData(),
    fn() => $this->serviceB->getData(),
]);
```

## Testing Standards

### Test Structure
- **PHPUnit**: Use PHPUnit for all tests
- **Test Location**: Tests in `tests/` directory within each component
- **Naming**: Test classes end with `Test.php`
- **Coverage**: Aim for high test coverage

### Test Patterns
```php
<?php

declare(strict_types=1);

namespace HyperfTest\ComponentName;

use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    public function testSomething(): void
    {
        $this->assertTrue(true);
    }
}
```

### Mocking & Stubs
- Use PHPUnit's built-in mocking
- Create stub classes for complex dependencies
- Mock external services and APIs

## Quality Standards

### Static Analysis
- **PHPStan**: Level 6 minimum
- **Type Hints**: Use strict typing everywhere
- **Return Types**: Always specify return types
- **Property Types**: Declare property types

### Code Review Guidelines
1. **PSR Compliance**: Ensure PSR standards adherence
2. **Performance**: Consider coroutine safety and performance impact
3. **Documentation**: Include PHPDoc for public APIs
4. **Error Handling**: Proper exception handling
5. **Testing**: Adequate test coverage

### Performance Considerations
- **Memory Usage**: Be mindful of memory in long-running processes
- **Coroutine Safety**: Ensure thread-safe operations
- **Connection Pooling**: Use connection pools for external resources
- **Caching**: Implement appropriate caching strategies

## Documentation

### Code Documentation
- **PHPDoc**: Use for all public methods and properties
- **Type Information**: Include parameter and return types
- **Examples**: Provide usage examples in docblocks

### API Documentation
- **Swagger/OpenAPI**: Use for HTTP APIs
- **Method Documentation**: Document all public methods
- **Configuration**: Document configuration options

## Security Guidelines

### Input Validation
- **Validation Component**: Use Hyperf validation for input
- **Sanitization**: Sanitize user inputs
- **Type Checking**: Validate parameter types

### Error Handling
- **Exception Handling**: Use appropriate exception types
- **Logging**: Log security-relevant events
- **Information Disclosure**: Don't expose sensitive information

## Migration & Compatibility

### Version Management
- **Semantic Versioning**: Follow SemVer for releases
- **BC Breaks**: Minimize breaking changes
- **Deprecation**: Use `@deprecated` annotations

### Legacy Support
- **PHP Versions**: Support current and previous PHP versions
- **Component Versions**: Maintain version compatibility matrix

## Common Issues & Solutions

### Memory Leaks
- **Context Clearing**: Clear context data appropriately
- **Event Listeners**: Remove listeners when not needed
- **Circular References**: Avoid circular dependencies

### Coroutine Issues
- **Blocking Operations**: Use coroutine-safe alternatives
- **Context Loss**: Properly pass context between coroutines
- **Resource Sharing**: Use appropriate synchronization

### Performance Optimization
- **Connection Reuse**: Reuse database/HTTP connections
- **Lazy Loading**: Load resources only when needed
- **Caching**: Cache expensive operations

## Development Workflow

### Local Development
1. Clone the repository
2. Use symbolic links for component development
3. Run tests with `co-phpunit`
4. Use PHP-CS-Fixer for code formatting

### Contributing
1. Fork the repository
2. Create feature branch
3. Write tests for new functionality
4. Ensure all tests pass
5. Submit pull request with clear description

### Release Process
- **Testing**: Comprehensive testing before release
- **Documentation**: Update documentation for changes
- **Changelog**: Maintain detailed changelog
- **Tagging**: Use semantic versioning for tags

## Examples

### Creating a Simple Component
```php
<?php

declare(strict_types=1);

namespace Hyperf\Example;

use Hyperf\Contract\ConfigInterface;

class ExampleService
{
    public function __construct(
        private readonly ConfigInterface $config
    ) {}

    public function doSomething(): string
    {
        return $this->config->get('example.message', 'Hello World');
    }
}
```

### Configuration Provider
```php
<?php

declare(strict_types=1);

namespace Hyperf\Example;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ExampleService::class => ExampleService::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for example.',
                    'source' => __DIR__ . '/../publish/example.php',
                    'destination' => BASE_PATH . '/config/autoload/example.php',
                ],
            ],
        ];
    }
}
```

This guide should help GitHub Copilot understand the Hyperf framework's architecture, coding standards, and development patterns to provide better assistance with Hyperf-related development tasks.