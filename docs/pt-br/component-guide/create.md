# Criar um novo componente

O `Hyperf` fornece ferramentas oficiais para criar rapidamente pacotes de componentes.

```
# Crie um pacote de componente que se adapte à versão mais recente do Hyperf
composer create-project hyperf/component-creator your_component dev-master

# Crie um pacote de componente que se adapte ao Hyperf 2.0
composer create-project hyperf/component-creator your_component "2.0.*"
```

## Usar pacotes de componentes não publicados no projeto

Suponha que o diretório do projeto seja o seguinte

```
/opt/project // diretório do projeto
/opt/your_component // diretório do pacote de componente
```

Supondo que o componente se chame `your_component/your_component`

Modifique `/opt/project/composer.json`

> Outras configurações irrelevantes foram omitidas abaixo

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
