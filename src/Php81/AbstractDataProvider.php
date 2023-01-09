<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource\Php81;

use ApiPlatform\State\ProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteDigital\ApiResource\Traits;
use WhiteDigital\EntityResourceMapper\Mapper\ClassMapper;
use WhiteDigital\EntityResourceMapper\Mapper\EntityToResourceMapper;
use WhiteDigital\EntityResourceMapper\Mapper\ResourceToEntityMapper;
use WhiteDigital\EntityResourceMapper\Security\AuthorizationService;

abstract class AbstractDataProvider implements ProviderInterface
{
    use Traits\AbstractDataProvider;

    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly ManagerRegistry $doctrine,
        protected readonly AuthorizationService $authorizationService,
        protected readonly ResourceToEntityMapper $resourceToEntityMapper,
        protected readonly EntityToResourceMapper $entityToResourceMapper,
        protected readonly ClassMapper $classMapper,
        protected readonly RequestStack $requestStack,
        protected readonly TranslatorInterface $translator,
        protected readonly Security $security,
        protected readonly iterable $collectionExtensions = [],
    ) {
    }
}
