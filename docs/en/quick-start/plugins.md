# IDE Plugins

## PhpStorm Plugins

### Database Plugin

You can install the [Hyperf Query](https://plugins.jetbrains.com/plugin/33333-hyperf-query) plugin in PhpStorm to provide database integration for the Hyperf query builder. It works with DataGrip to provide autocompletion for database schemas, tables, views and columns:

- Schemas, tables, views and columns completion for query and schema builder methods
- Completion for migrations
- Inspection of unknown database elements
- Table alias support
- Table name resolving from model for builder methods
- Model relation table name resolving for builder relation closure methods
- Text linking with database elements for navigation (Ctrl+Click)
- Configurable table prefix and datasource filtering

Installation: `Preferences` > `Plugins` > `Marketplace`, search for **Hyperf Query**, then click **Install Plugin**. After installation, it is recommended to configure your data sources and filter the ones you want to use in the plugin settings.

> This plugin is a fork of [laravel-query-intellij](https://github.com/ekvedaras/laravel-query-intellij) (MIT licensed), adapted to target `Hyperf\Database\*` instead of `Illuminate\Database\*`. The source code is available at [hyperf-query-intellij](https://github.com/tw2066/hyperf-query-intellij).
