<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource\Maker;

use Exception;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Mapper\ClassMapper;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;

use function array_column;
use function array_multisort;
use function array_unique;
use function class_exists;
use function dirname;
use function getcwd;
use function is_subclass_of;
use function preg_replace;
use function sort;
use function sprintf;
use function str_replace;
use function strtolower;
use function unlink;

use const SORT_ASC;
use const SORT_REGULAR;

class MakeApiResource extends AbstractMaker
{
    public function __construct(
        private readonly ClassMapper $mapper,
        private readonly ParameterBagInterface $bag,
    ) {
    }

    public static function getCommandName(): string
    {
        return 'make:api-resource';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a new ApiResource, DataProvider and DataProcessor. And regenerates ClassMapperConfigurator';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('entity', InputArgument::OPTIONAL, 'The name of the entity class (e.g. <fg=yellow>User</>) for which to create resource')
            ->setHelp('
<info>php %command.full_name% User</info>

If the argument is missing, the command will ask for the entity class name interactively.
            ');
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        $dependencies->addClassDependency(
            BaseResource::class,
            'whitedigital-eu/entity-resource-mapper-bundle',
        );
    }

    /**
     * @throws Exception
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $entityName = $input->getArgument('entity');
        $ns = '\\';
        $wd = 'whitedigital.api_resource';

        $entity = $generator->createClassNameDetails($entityName, $this->bag->get($wd . '.namespace.entity') . $ns);

        if (!is_subclass_of($entity->getFullName(), BaseEntity::class)) {
            $io->caution(sprintf('%s does not exist or it does not extend BaseEntity', $entity->getShortName()));

            exit;
        }

        $provider = $generator->createClassNameDetails($entityName, ($dpn = $this->bag->get($wd . '.namespace.data_provider')) . $ns, $dpn);

        $resource = $generator->createClassNameDetails($entityName, ($arn = $this->bag->get($wd . '.namespace.api_resource')) . $ns, $arn);

        $processor = $generator->createClassNameDetails($entityName, ($pn = $this->bag->get($wd . '.namespace.data_processor')) . $ns, $pn);

        if (class_exists($provider->getFullName(), false) || class_exists($resource->getFullName(), false) || class_exists($processor->getFullName(), false)) {
            $io->caution(sprintf('%s, %s or %s already exists', $resource->getShortName(), $processor->getShortName(), $provider->getShortName()));

            exit;
        }

        $phpVersion = 'Php82';
        if (80200 > $this->bag->get($wd . '.php_version')) {
            $phpVersion = 'Php81';
        }

        $generator->generateClass(
            $provider->getFullName(),
            dirname(__DIR__, 2) . '/skeleton/apiresource/DataProvider.tpl.php',
            [
                'php' => $phpVersion,
                'resource' => $resource,
            ],
        );

        $generator->generateClass(
            $processor->getFullName(),
            dirname(__DIR__, 2) . '/skeleton/apiresource/DataProcessor.tpl.php',
            [
                'entity' => $entity,
                'php' => $phpVersion,
                'resource' => $resource,
            ],
        );

        $generator->generateClass(
            $resource->getFullName(),
            dirname(__DIR__, 2) . '/skeleton/apiresource/ApiResource.tpl.php',
            [
                'entity_name' => $entityName,
                'prefix' => $this->toSnakeCase($entityName, $this->bag->get($wd . '.defaults.space')),
                'processor' => $processor,
                'provider' => $provider,
                'separator' => $this->bag->get($wd . '.defaults.role_separator'),
            ],
        );

        $generator->writeChanges();

        $mapping = $uses = [];
        foreach ($this->mapper->getMap() as $map) {
            if (null !== ($condition = $map['condition'])) {
                $class = $generator->createClassNameDetails($ns . $condition, '');
                if (class_exists($class->getFullName())) {
                    $uses[] = $class->getFullName();
                    $condition = $class->getShortName() . '::class';
                } else {
                    $condition = sprintf("'%s'", $condition);
                }
            }

            $mapping[] = [
                'entity' => ($e = $generator->createClassNameDetails($ns . $map['entity'], ''))->getShortName() . '::class',
                'dto' => ($d = $generator->createClassNameDetails($ns . $map['dto'], ''))->getShortName() . '::class',
                'condition' => $condition,
            ];
            $uses[] = $e->getFullName();
            $uses[] = $d->getFullName();
        }

        $uses[] = $entity->getFullName();
        $uses[] = $resource->getFullName();

        $mapping[] = [
            'entity' => $entity->getShortName() . '::class',
            'dto' => $resource->getShortName() . '::class',
            'condition' => null,
        ];

        $configurator = $generator->createClassNameDetails('ClassMapperConfigurator', $this->bag->get($wd . '.namespace.class_map_configurator'));
        if (class_exists($configurator->getFullName())) {
            unlink($this->fixPath($configurator->getFullName()));
        }

        $uses = array_unique($uses, SORT_REGULAR);
        sort($uses);
        array_multisort(array_column(array_unique($mapping, SORT_REGULAR), 'dto'), SORT_ASC, $mapping);

        $generator->generateClass(
            $configurator->getFullName(),
            dirname(__DIR__, 2) . '/skeleton/apiresource/ClassMapperConfigurator.tpl.php',
            [
                'uses' => $uses,
                'mapping' => $mapping,
            ],
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
    }

    private function fixPath(string $className): string
    {
        $className = preg_replace('#' . $this->bag->get('whitedigital.api_resource.namespace.root') . '\\\\#', '', $className, 1);
        $className = str_replace('\\', '/', $className);

        return getcwd() . '/src/' . $className . '.php';
    }

    private function toSnakeCase(string $string, string $space): string
    {
        $string = preg_replace(pattern: [
            '#([A-Z\d]+)([A-Z][a-z])#',
            '#([a-z\d])([A-Z])#',
        ], replacement: '\1_\2', subject: $string);

        return strtolower(string: str_replace(search: '-', replace: $space, subject: (string) $string));
    }
}
