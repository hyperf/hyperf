# Create new component

`Hyperf` Official tools are provided to quickly create component packages.

```
# Create a component package adapted to the latest version of Hyperf
composer create-project hyperf/component-creator your_component dev-master

# Create a package for Hyperf 3.0
composer create-project hyperf/component-creator your_component "3.0.*"
```

## Using an unpublished component package in a project

Suppose the project directory is as follows

```
/opt/project // project directory
/opt/your_component // Component package directory
```

Suppose the component is named `your_component/your_component`

Revise /opt/project/composer.json

> Other irrelevant configurations are omitted below

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
