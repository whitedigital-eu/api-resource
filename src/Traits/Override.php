<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource\Traits;

use ReflectionClass;
use ReflectionException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use WhiteDigital\EntityResourceMapper\Security\AuthorizationService;
use WhiteDigital\EntityResourceMapper\Security\Enum\GrantType;

use function array_key_exists;
use function array_merge;

trait Override
{
    protected function override(string $operation, string $class): bool
    {
        try {
            $property = (new ReflectionClass($this->authorizationService))->getProperty('resources')->getValue($this->authorizationService);
        } catch (ReflectionException) {
            return false;
        }

        if (isset($property[$class])) {
            $attributes = $property[$class];
        } else {
            return false;
        }

        $allowed = array_merge($attributes[AuthorizationService::ALL] ?? [], $attributes[$operation] ?? []);
        if ([] !== $allowed && array_key_exists(AuthenticatedVoter::PUBLIC_ACCESS, $allowed)) {
            if (GrantType::ALL === $allowed[AuthenticatedVoter::PUBLIC_ACCESS]) {
                return true;
            }

            throw new InvalidConfigurationException('Public access only allowed with "all" grant type');
        }

        return false;
    }
}
