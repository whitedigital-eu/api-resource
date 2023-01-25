<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Vich\UploaderBundle\Naming\SmartUniqueNamer;
use WhiteDigital\ApiResource\DependencyInjections\Traits\DefineApiPlatformMappings;
use WhiteDigital\ApiResource\DependencyInjections\Traits\DefineOrmMappings;

use function array_merge_recursive;
use function explode;

use const PHP_VERSION_ID;

class ApiResourceBundle extends AbstractBundle
{
    use DefineApiPlatformMappings;
    use DefineOrmMappings;

    private const MAPPINGS = [
        'type' => 'attribute',
        'dir' => __DIR__ . '/Entity',
        'alias' => 'ApiResource',
        'prefix' => 'WhiteDigital\ApiResource\Entity',
        'is_bundle' => false,
        'mapping' => true,
    ];

    private const PATHS = [
        '%kernel.project_dir%/vendor/whitedigital-eu/api-resource/src/ApiResource',
    ];

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (true === ($config['enabled'] ?? false)) {
            foreach ((new Functions())->makeOneDimension(['whitedigital.api_resource' => $config], onlyLast: true) as $key => $value) {
                $builder->setParameter($key, $value);
            }

            $container->import('../config/services.php');
        }

        $container->import('../config/decorator.php');
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition
            ->rootNode()
            ->canBeDisabled()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('php_version')
                    ->defaultValue(PHP_VERSION_ID)
                    ->beforeNormalization()
                        ->ifString()
                        ->then(static function ($v) {
                            $version = explode('.', $v);

                            return (int) $version[0] * 10000 + (int) $version[1] * 100 + (int) $version[2];
                        })
                    ->end()
                ->end()
                ->arrayNode('namespaces')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('api_resource')->defaultValue('ApiResource')->end()
                        ->scalarNode('class_map_configurator')->defaultValue('Service\\Configurator')->end()
                        ->scalarNode('data_processor')->defaultValue('DataProcessor')->end()
                        ->scalarNode('data_provider')->defaultValue('DataProvider')->end()
                        ->scalarNode('entity')->defaultValue('Entity')->end()
                        ->scalarNode('root')->defaultValue('App')->end()
                    ->end()
                ->end()
                ->arrayNode('defaults')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('api_resource_suffix')->defaultValue('Resource')->end()
                        ->scalarNode('role_separator')->defaultValue(':')->end()
                        ->scalarNode('space')->defaultValue('_')->end()
                    ->end()
                ->end()
                ->booleanNode('enable_storage')->defaultFalse()->end()
                ->scalarNode('entity_manager')->defaultValue('default')->end()
            ->end();
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $apiResource = array_merge_recursive(...$builder->getExtensionConfig('api_resource') ?? []);
        $audit = array_merge_recursive(...$builder->getExtensionConfig('whitedigital') ?? [])['audit'] ?? [];

        if (true === ($apiResource['enabled'] ?? true)) {
            if (true === ($apiResource['enable_storage'] ?? false)) {
                $mappings = $this->getOrmMappings($builder, $apiResource['entity_manager'] ?? 'default');

                $this->addDoctrineConfig($container, $apiResource['entity_manager'] ?? 'default', $mappings, 'ApiResource', self::MAPPINGS);
                $this->addApiPlatformPaths($container, self::PATHS);

                if (true === ($audit['enabled'] ?? false)) {
                    $this->addDoctrineConfig($container, $audit['audit_entity_manager'], $mappings, 'ApiResource', self::MAPPINGS);
                }

                $container->extension('vich_uploader', [
                    'mappings' => [
                        'wd_ar_media_object' => [
                            'uri_prefix' => '/storage',
                            'upload_destination' => '%kernel.project_dir%/public/wd/storage',
                            'inject_on_load' => false,
                            'namer' => SmartUniqueNamer::class,
                        ],
                    ],
                ]);
            }
        }
    }
}
