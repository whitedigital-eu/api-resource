<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function explode;

use const PHP_VERSION_ID;

class ApiResourceBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (true === ($config['enabled'] ?? false)) {
            $builder->setParameter('whitedigital.api_resource.namespace.api_resource', $config['namespaces']['api_resource']);
            $builder->setParameter('whitedigital.api_resource.namespace.class_map_configurator', $config['namespaces']['class_map_configurator']);
            $builder->setParameter('whitedigital.api_resource.namespace.data_provider', $config['namespaces']['data_provider']);
            $builder->setParameter('whitedigital.api_resource.namespace.data_processor', $config['namespaces']['data_processor']);
            $builder->setParameter('whitedigital.api_resource.namespace.entity', $config['namespaces']['entity']);
            $builder->setParameter('whitedigital.api_resource.namespace.root', $config['namespaces']['root']);
            $builder->setParameter('whitedigital.api_resource.php_version', $config['php_version']);
            $builder->setParameter('whitedigital.api_resource.defaults.space', $config['defaults']['space']);
            $builder->setParameter('whitedigital.api_resource.defaults.role_separator', $config['defaults']['role_separator']);

            $container->import('../config/services.php');
        }
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition
            ->rootNode()
            ->canBeEnabled()
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
                        ->scalarNode('role_separator')->defaultValue(':')->end()
                        ->scalarNode('space')->defaultValue('_')->end()
                    ->end()
                ->end()
            ->end();
    }
}
