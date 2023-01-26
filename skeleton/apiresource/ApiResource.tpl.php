<?php echo "<?php declare(strict_types = 1);\n"; ?>

namespace <?php echo $namespace; ?>;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use <?php echo $processor->getFullName() . ";\n"; ?>
use <?php echo $provider->getFullName() . ";\n"; ?>
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Serializer\Annotation\Groups;
use WhiteDigital\ApiResource\ApiResource\Traits as ARTraits;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;

#[
    ApiResource(
        shortName: '<?php echo $entity_name; ?>',
        operations: [
            new Delete(
                requirements: ['id' => '\d+', ],
            ),
            new Get(
                requirements: ['id' => '\d+', ],
                normalizationContext: ['groups' => [<?php echo $class_name; ?>::ITEM, ], ],
            ),
            new GetCollection(
                normalizationContext: ['groups' => [<?php echo $class_name; ?>::READ, ], ],
            ),
            new Patch(
                requirements: ['id' => '\d+', ],
                denormalizationContext: ['groups' => [<?php echo $class_name; ?>::PATCH, ], ],
            ),
            new Post(
                denormalizationContext: ['groups' => [<?php echo $class_name; ?>::WRITE, ], ],
            ),
        ],
        normalizationContext: ['groups' => [<?php echo $class_name; ?>::READ, ], ],
        denormalizationContext: ['groups' => [<?php echo $class_name; ?>::WRITE, ], ],
        order: ['createdAt' => Criteria::DESC, ],
        provider: <?php echo $provider->getShortName(); ?>::class,
        processor: <?php echo $processor->getShortName(); ?>::class,
    )
]
class <?php echo $class_name; ?> extends BaseResource
{
    use ARTraits\CreatedUpdated;
    use ARTRaits\Groups;

    public const PREFIX = '<?php echo $prefix . $separator; ?>';

    #[ApiProperty(identifier: true)]
    #[Groups([self::READ, self::ITEM, ])]
    public mixed $id = null;
}
