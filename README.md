# ApiResource

### What is it?

ApiResource is a package for different logical parts to make usage or configuration
of other `whitedigital-eu` packages easier taking away the need to copy same code
in multiple packages to cover same or similar functionallity.

### System Requirements
PHP 8.2+  
Symfony 6.2+

### Installation
The recommended way to install is via Composer:

```shell
composer require whitedigital-eu/api-resource
```

### Configuration
Configuration for different parts are described below. By default this package comes
enabled, so you don't need to configure anything to start using this package. 
Without any configuration changes you can use all but 3rd point from below.

---
# Logical parts covered by this package

## 1. ApiResource maker

ApiResource maker is custom symfony maker to make creation of Api Platform resources, providers and processors easier
if used together with `whitedigital-eu/entity-resource-mapper-bundle`.

Default configuration options comes based on `api-platform`|`symfony` recommendations but you can override them like this (default values shown):
```yaml
api_resource:
    namespaces:
        api_resource: ApiResource
        class_map_configurator: Service\\Configurator # required by whitedigital-eu/entity-resource-mapper-bundle
        data_processor: DataProcessor
        data_provider: DataProvider
        entity: Entity
        root: App
    defaults:
        api_resource_suffix: Resource
        role_separator: ':'
        space: '_'
```
```php
use Symfony\Config\ApiResourceConfig;

return static function (ApiResourceConfig $config): void {
    $namespaces = $config
        ->namespaces();

    $namespaces
        ->apiResource('ApiResource')
        ->classMapConfigurator('Service\\Configurator') # required by whitedigital-eu/entity-resource-mapper-bundle
        ->dataProcessor('DataProcessor')
        ->dataProvider('DataProvider')
        ->entity('Entity')
        ->root('App');
        
    $defaults = $config
        ->defaults();
        
    $defaults
        ->apiResourceSuffix('Resource')
        ->roleSeparator(':')
        ->space('_');
};
```
`namespaces` are there to set up different directories for generated files. So, if you need to put files in different directories/namespaces, you can chnage it as such.

`roleSeparator` and `space` from `defaults` are added to configure separators for groups used in api resource. For example, `UserRole` with defaults will become `user_role:read` for read group.  
`apiResourcrSuffix` defines suffix for api resource class name. For example, by default `User` entity will make `UserResource` api resource class.

### Usage
Simply run `make:api-resource <EntityName>` where EntityName is entity you want to create api resource for. 
Example, `make:api-resource User` to make UserResource, UserDataProcessor and UserDataProvider for User entity.

---
## 2. Base provider and processor
In most cases way how to read or write data to database is the same, so this package provides `AbstractDataProcessor` 
and `AbstractDataProvider` that implements base logic for api platform when used with `whitedigital-eu/entity-resource-mapper-bundle`.
Maker part of this package uses these clases for generation as well. Using these abstractions will take away need to
duplicate code for each entity/resource. As these are abstractions, you can always override any function of them when
needed.

---
## 3. Storage item upload
With the help of `vich/uploader-bundle` this package enables file upload when used with api platform. This is the 
only part of this package that does not come enabled by default. To enable this, configure:
```yaml
api_resource:
    enable_storage: true
```
```php
use Symfony\Config\ApiResourceConfig;

return static function (ApiResourceConfig $config): void {
    $config
        ->enableStorage(true);
};
```
After this, you need to update your database schema to use Audit entity.  
If using migrations:
```shell
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
```
If by schema update:
```shell
bin/console doctrine:schema:update --force
``` 
This will enable new `StorageItem` api resource with `/api/wd/ar/storage_items` iri. If you want different iri, see
below how to override it.

---
## 4. Extended api resource
Other `whitedigital-eu` packages may come with api resources that with some configuration may not be suited for 
straight away usage in a project. This is why `ExtendedApiResource` is useful to override part of options defined
in default attributes.  

For example, take a look at `WhiteDigital\ApiResource\ApiResource\StorageItemResource` class. It defines api resource
with `routePrefix: /wd/ar` which means that iri generated for it will be `/api/wd/ar/storage_items`. If you want iri
to be `/api/storage_items`, you have to do the following:
1. Create new class that extends resource you want to override
2. Add `ExtendedApiResouce` attribute insted of `ApiResource` attribute
3. Pass only those options that you want to override, others will be taken from resource you are extending
```php
namespace App\ApiResource;

use WhiteDigital\ApiResource\ApiResource\StorageItemResource as WDStorageItemResource;
use WhiteDigital\ApiResource\Attribute\ExtendedApiResource;

#[ExtendedApiResource(routePrefix: '')]
class StorageItemResource extends WDStorageItemResource
{
}
```
`ExtendedApiResouce` attribute checks which resource you are extending and overrides options given in extension,
keeping other options same as in parent resource.

> **IMPORTANT**: You need to disable bundled resource in configuration, otherwise you will have 2 instances of audit
> resource: one with `/api/storage_items` iri and one with `/api/wd/ar/storage_items` iri.

```yaml
api_resource:
    enable_storage: true
    enable_storage_resource: false
```
```php
use Symfony\Config\ApiResourceConfig;

return static function (ApiResourceConfig $config): void {
    $config
        ->enableStorage(true)
        ->enableStorageResource(false);
};
```
> **IMPORTANT**: If used for extending other resources not defined by `whitedigital-eu` you must logic stays same:
> If parent resource is not disabled, you will have two seperate resources with same logic and different iris
