# Creating a New Component

`Hyperf` provides tools to quickly create component packages.

```bash
# Create a component package that adapts to the latest version of Hyperf
composer create-project hyperf/component-creator your_component dev-master

# Create a component package that adapts to Hyperf 2.0 version
composer create-project hyperf/component-creator your_component "2.0.*"
```

## Using Unpublished Component Packages in a Project

Suppose the project directory is as follows:

```
/opt/project // Project directory
/opt/your_component // Component package directory
```

Assume the component name is `your_component/your_component`.

Modify `/opt/project/composer.json`:

> Other irrelevant configurations are omitted below.

```json
{
    "require": {
        "your_component/your_component": "dev-master"
    },
    "repositories": {
        "your_component": {
            "type": "path",
            "url": "/opt/your_component"
        }
    }
}
```

Finally, execute `composer update -o` in the directory `/opt/project`.
