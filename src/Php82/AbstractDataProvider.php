<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource\Php82;

use ApiPlatform\State\ProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteDigital\ApiResource\Traits;
use WhiteDigital\EntityResourceMapper\Mapper\ClassMapper;
use WhiteDigital\EntityResourceMapper\Mapper\EntityToResourceMapper;
use WhiteDigital\EntityResourceMapper\Mapper\ResourceToEntityMapper;
use WhiteDigital\EntityResourceMapper\Security\AuthorizationService;

abstract readonly class AbstractDataProvider implements ProviderInterface
{
    use Traits\AbstractDataProvider;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected ManagerRegistry $doctrine,
        protected AuthorizationService $authorizationService,
        protected ResourceToEntityMapper $resourceToEntityMapper,
        protected EntityToResourceMapper $entityToResourceMapper,
        protected ClassMapper $classMapper,
        protected RequestStack $requestStack,
        protected TranslatorInterface $translator,
        protected Security $security,
        protected ParameterBagInterface $bag,
        protected iterable $collectionExtensions = [],
    ) {
    }
}
