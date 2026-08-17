# Criar um novo componente

O `Hyperf` oferece oficialmente ferramentas para criar rapidamente pacotes de componentes.

```
# Create a component package that adapts to the latest version of Hyperf
composer create-project hyperf/component-creator your_component dev-master

# Create a component package that adapts to Hyperf 2.0 version
composer create-project hyperf/component-creator your_component "2.0.*"
```

## Usando pacotes de componentes não publicados no projeto

Suponha que o diretório do projeto seja o seguinte

```
/opt/project // project directory
/opt/your_component // component package directory
```

Supondo que o componente se chame `your_component/your_component`

Modifique /opt/project/composer.json

> Demais configurações irrelevantes foram omitidas abaixo

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

Por fim, execute `composer update -o` no diretório `/opt/project`.