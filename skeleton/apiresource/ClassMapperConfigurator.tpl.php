<?php echo "<?php declare(strict_types = 1);\n"; ?>

namespace <?php echo $namespace; ?>;

<?php
    foreach ($uses as $use) {
        echo 'use ' . $use . ";\n";
    }
?>
use WhiteDigital\EntityResourceMapper\Mapper\ClassMapper;
use WhiteDigital\EntityResourceMapper\Mapper\ClassMapperConfiguratorInterface;

class ClassMapperConfigurator implements ClassMapperConfiguratorInterface
{
    public function __invoke(ClassMapper $classMapper): void
    {
<?php
    foreach ($mapping as $map) {
        echo '        $classMapper->registerMapping(' . $map['dto'] . ', ' . $map['entity'] . ', ' . $map['condition'] . ');' . "\n";
    }
?>
    }
}
