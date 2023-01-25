<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource\Php81;

use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteDigital\ApiResource\Traits;
use WhiteDigital\EntityResourceMapper\Mapper\EntityToResourceMapper;
use WhiteDigital\EntityResourceMapper\Mapper\ResourceToEntityMapper;
use WhiteDigital\EntityResourceMapper\Security\AuthorizationService;

abstract class AbstractDataProcessor implements ProcessorInterface
{
    use Traits\AbstractDataProcessor;

    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly ResourceToEntityMapper $resourceToEntityMapper,
        protected readonly EntityToResourceMapper $entityToResourceMapper,
        protected readonly AuthorizationService $authorizationService,
        protected readonly ParameterBagInterface $bag,
        protected readonly TranslatorInterface $translator,
    ) {
    }
}
