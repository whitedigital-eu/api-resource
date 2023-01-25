<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource\DependencyInjections\Traits;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function array_unique;

trait DefineApiPlatformMappings
{
    protected function addApiPlatformPaths(ContainerConfigurator $container, array $bundlePaths): void
    {
        $paths = array_unique($bundlePaths);

        $container->extension('api_platform', [
            'mapping' => [
                'paths' => $paths,
            ],
        ]);
    }
}
