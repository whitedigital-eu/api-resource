<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource\Attribute;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\State\OptionsInterface;
use Attribute;
use PhpToken;
use ReflectionClass;
use ReflectionException;

use function count;
use function debug_backtrace;
use function file_get_contents;
use function func_get_args;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ExtendedApiResource extends ApiResource
{
    /**
     * @throws ReflectionException
     */
    public function __construct(?string $uriTemplate = null, ?string $shortName = null, ?string $description = null, array|string|null $types = null, $operations = null, $formats = null, $inputFormats = null, $outputFormats = null, $uriVariables = null, ?string $routePrefix = null, ?array $defaults = null, ?array $requirements = null, ?array $options = null, ?bool $stateless = null, ?string $sunset = null, ?string $acceptPatch = null, ?int $status = null, ?string $host = null, ?array $schemes = null, ?string $condition = null, ?string $controller = null, ?string $class = null, ?int $urlGenerationStrategy = null, ?string $deprecationReason = null, ?array $cacheHeaders = null, ?array $normalizationContext = null, ?array $denormalizationContext = null, ?bool $collectDenormalizationErrors = null, ?array $hydraContext = null, ?array $openapiContext = null, OpenApiOperation|bool|null $openapi = null, ?array $validationContext = null, ?array $filters = null, ?bool $elasticsearch = null, $mercure = null, $messenger = null, $input = null, $output = null, ?array $order = null, ?bool $fetchPartial = null, ?bool $forceEager = null, ?bool $paginationClientEnabled = null, ?bool $paginationClientItemsPerPage = null, ?bool $paginationClientPartial = null, ?array $paginationViaCursor = null, ?bool $paginationEnabled = null, ?bool $paginationFetchJoinCollection = null, ?bool $paginationUseOutputWalkers = null, ?int $paginationItemsPerPage = null, ?int $paginationMaximumItemsPerPage = null, ?bool $paginationPartial = null, ?string $paginationType = null, ?string $security = null, ?string $securityMessage = null, ?string $securityPostDenormalize = null, ?string $securityPostDenormalizeMessage = null, ?string $securityPostValidation = null, ?string $securityPostValidationMessage = null, ?bool $compositeIdentifier = null, ?array $exceptionToStatus = null, ?bool $queryParameterValidationEnabled = null, ?array $graphQlOperations = null, $provider = null, $processor = null, ?OptionsInterface $stateOptions = null, array $extraProperties = [])
    {
        $callerClass = $this->getCallerClass(debug_backtrace()[0]['file'] ?? null);
        $attributes = func_get_args();
        if (null !== $callerClass) {
            $caller = new ReflectionClass($callerClass);
            $parent = $caller->getParentClass();
            $attributes = $parent->getAttributes(ApiResource::class)[0]->getArguments();
        }
        $current = (new ReflectionClass(__CLASS__))->getMethod(__FUNCTION__);
        $i = 0;
        $args = func_get_args();

        foreach ($current->getParameters() as $parameter) {
            if (isset($args[$i]) && $parameter->getDefaultValue() !== $args[$i]) {
                $attributes[$parameter->getName()] = $args[$i];
            }
            $i++;
        }

        parent::__construct(...$attributes);
    }

    private function getCallerClass(?string $file): ?string
    {
        if (null === $file) {
            return null;
        }

        $namespace = '';
        $tokens = PhpToken::tokenize(file_get_contents($file));

        for ($i = 0, $c = count($tokens); $i < $c; $i++) {
            if ('T_NAMESPACE' === $tokens[$i]->getTokenName()) {
                for ($j = $i + 1; $j < $c; $j++) {
                    if ('T_NAME_QUALIFIED' === $tokens[$j]->getTokenName()) {
                        $namespace = $tokens[$j]->text;
                        break;
                    }
                }
            }

            if ('T_CLASS' === $tokens[$i]->getTokenName()) {
                for ($j = $i + 1; $j < $c; $j++) {
                    if ('T_WHITESPACE' === $tokens[$j]->getTokenName()) {
                        continue;
                    }

                    if ('T_STRING' === $tokens[$j]->getTokenName()) {
                        return $namespace . '\\' . $tokens[$j]->text;
                    }
                    break;
                }
            }
        }

        return null;
    }
}
