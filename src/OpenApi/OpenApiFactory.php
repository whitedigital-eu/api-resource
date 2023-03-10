<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\OpenApi;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use function str_starts_with;

#[AsDecorator(decorates: 'api_platform.openapi.factory')]
final readonly class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
        private ParameterBagInterface $bag,
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        if (!$this->validate()) {
            $filteredPaths = new Model\Paths();
            foreach ($openApi->getPaths()->getPaths() as $path => $pathItem) {
                if (str_starts_with($path, '/api/wd/ar/')) {
                    continue;
                }

                $filteredPaths->addPath($path, $pathItem);
            }

            return $openApi->withPaths($filteredPaths);
        }

        return $openApi;
    }

    private function validate(): bool
    {
        if ($this->bag->has($keyBundle = 'whitedigital.api_resource.enabled') && true === $this->bag->get($keyBundle)) {
            if ($this->bag->has($keyStorage = 'whitedigital.api_resource.enable_storage') && true === $this->bag->get($keyStorage)) {
                return $this->bag->has($keyResource = 'whitedigital.api_resource.enable_storage_resource') && true === $this->bag->get($keyResource);
            }
        }

        return false;
    }
}
