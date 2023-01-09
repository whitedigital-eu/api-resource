# ApiResource Maker

### What is it?

ApiResource maker is custom symfony maker to make creation of Api Platform resources, providers and processors easier
if used in conjunction with whitedigital-eu/entity-resource-mapper-bundle.

### System Requirements
PHP 8.1+  
Symfony 6.1+

### Installation
The recommended way to install is via Composer:

```shell
composer require whitedigital-eu/api-resource
```
---
### Configuration

By default, this bundle is enabled after installation and adds `make:api-resource` command.
Default configuration options comes based on api-platform|symfony recommendations but you can override them like this (default values shown):
```yaml
api_resource:
    php_version: 80200
    namespaces:
        root: App
        api_resource: ApiResource
        data_provider: DataProvider
        data_processor: DataProcessor
        entity: Entity
        class_map_configurator: Service\\ClassMapConfigurator # required by whitedigital-eu/entity-resource-mapper-bundle
    defaults:
        role_separator: ':'
        space: '_'
```
```php
use Symfony\Config\ApiResourceConfig;

return static function (ApiResourceConfig $config): void {
    $config
        ->phpVersion(80200);

    $namespaces = $config
        ->namespaces();

    $namespaces
        ->root('App')
        ->apiResource('ApiResource')
        ->dataProvider('DataProvider')
        ->dataProcessor('DataProcessor')
        ->entity('Entity')
        ->classMapConfigurator('Service\\Configurator');
        
    $defaults = $config
        ->defaults();
        
    $defaults
        ->roleSeparator(':')
        ->space('_');
};
```
`phpVersion` takes int|string as input, so you can do 80200 or 8.2.0 and it will work. Php version variable configuration is required as this bundle contains some 
php features introduced in php 8.2. So, if you need to use this bundle with php 8.1, set it in this parameter. Default value is taken from `PHP_VERSION_ID`.  

`namespaces` are there to set up different directories for generated files. So, if you need to put files in different directories/namespaces, you can chnage it as such.  

`defaults` are added to configure separators for groups used in api resource. For example, `UserRole` with defaults will become `user_role:read` for read group.  

---
### Usage
Simply run `make:api-resource <EntityName>` where EntityName is entity you want to create api resource for. Example, `make:api-resource User` to make UserApiResource for User entity.
