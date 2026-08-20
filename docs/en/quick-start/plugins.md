# IDE Plugins

## PhpStorm Plugins

### Hyperf Base Plugin

You can install the [Hyperf Base](https://github.com/tw2066/idea-plugin-hyperf) plugin in PhpStorm to get code completion, navigation and quick command support for the Hyperf framework. Key features:

- Routing: completion and navigation for `Controller@action` in `Router::get/post/...`
- Config keys: indexing, completion and navigation for the `config()` helper and `ConfigInterface::get()/has()` (supports 3.1+ subdirectories and dot-separated file names)
- Translation keys: indexing, completion and navigation for `trans()` / `__()` and `TranslatorInterface::trans()`
- Environment variables: completion and navigation for `env()` keys (indexes the project `.env` file)
- Validation rules: completion and Chinese hover documentation for rule strings in `FormRequest::rules()`, `ValidatorFactory::make()/validate()` and `$scenes`
- BASE_PATH paths: completion and navigation for directories/files in `BASE_PATH . '/a/b'` concatenation chains
- View templates: completion and navigation for template names in `view()`, `RenderInterface::render()`, etc. (dot syntax + `pkg::name` namespaces)
- AOP aspects: navigation and method-name completion for `'FQN::method'` strings in `#[Aspect]` attributes and `AbstractAspect` properties
- Cache listeners: completion and mutual navigation for listener names between `#[Cacheable(listener: "...")]` and `DeleteListenerEvent`
- DI bindings: hovering over an interface shows a link to the effective implementation class in the documentation popup
- Crontab: completion and navigation for `callback` method names; hovering over a `rule` expression shows the next 5 execution times
- Hyperf top-level menu: run code generation (`gen:*`) and common commands (`migrate`, `start`, `describe:routes`, etc.) in the built-in Terminal with one click
- Command line marker: a run button next to the class name of `Hyperf\Command\Command` subclasses; click to execute the command directly

> Only supports PhpStorm 2026.2 and above

### Database Plugin

You can install the [Hyperf Query](https://github.com/tw2066/hyperf-query-intellij) plugin in PhpStorm to provide database integration for the Hyperf query builder. It works with DataGrip to provide autocompletion for database schemas, tables, views and columns:

- Schemas, tables, views and columns completion for query and schema builder methods
- Completion for migrations
- Inspection of unknown database elements
- Table alias support
- Table name resolving from model for builder methods
- Model relation table name resolving for builder relation closure methods
- Text linking with database elements for navigation (Ctrl+Click)
- Configurable table prefix and datasource filtering

Installation: `Preferences` > `Plugins` > `Marketplace`, search for **Hyperf Base** / **Hyperf Query**, then click **Install Plugin**. After installation, it is recommended to configure your data sources and filter the ones you want to use in the plugin settings.
