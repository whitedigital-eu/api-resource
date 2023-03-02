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
                normalizationContext: ['groups' => [self::ITEM, ], ],
            ),
            new GetCollection(
                normalizationContext: ['groups' => [self::READ, ], ],
            ),
            new Patch(
                requirements: ['id' => '\d+', ],
                denormalizationContext: ['groups' => [self::PATCH, ], ],
            ),
            new Post(
                denormalizationContext: ['groups' => [self::WRITE, ], ],
            ),
        ],
        normalizationContext: ['groups' => [self::READ, ], ],
        denormalizationContext: ['groups' => [self::WRITE, ], ],
        order: ['createdAt' => Criteria::DESC, ],
        provider: <?php echo $provider->getShortName(); ?>::class,
        processor: <?php echo $processor->getShortName(); ?>::class,
    )
]
class <?php echo $class_name; ?> extends BaseResource
{
    use ARTraits\CreatedUpdated;

    public const PREFIX = '<?php echo $prefix . $separator; ?>';

    private const ITEM = self::PREFIX . 'item'; // <?php echo $prefix . $separator . 'item' . "\n"; ?>
    private const PATCH = self::PREFIX . 'patch'; // <?php echo $prefix . $separator . 'patch' . "\n"; ?>
    private const READ = self::PREFIX . 'read'; // <?php echo $prefix . $separator . 'read' . "\n"; ?>
    private const WRITE = self::PREFIX . 'write'; // <?php echo $prefix . $separator . 'write' . "\n"; ?>

    #[ApiProperty(identifier: true)]
    #[Groups([self::READ, self::ITEM, ])]
    public mixed $id = null;
}
