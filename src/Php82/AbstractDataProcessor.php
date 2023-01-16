<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource\Php82;

use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use WhiteDigital\ApiResource\Traits;
use WhiteDigital\EntityResourceMapper\Mapper\EntityToResourceMapper;
use WhiteDigital\EntityResourceMapper\Mapper\ResourceToEntityMapper;
use WhiteDigital\EntityResourceMapper\Security\AuthorizationService;

abstract readonly class AbstractDataProcessor implements ProcessorInterface
{
    use Traits\AbstractDataProcessor;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected ResourceToEntityMapper $resourceToEntityMapper,
        protected EntityToResourceMapper $entityToResourceMapper,
        protected AuthorizationService $authorizationService,
        protected ParameterBagInterface $bag,
    ) {
    }
}
