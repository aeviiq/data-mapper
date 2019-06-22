<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper;

use Aeviiq\DataMapper\Exception\InvalidArgumentException;
use Aeviiq\DataMapper\Schema\Builder\Builder;
use Aeviiq\DataMapper\Schema\Schema;
use Aeviiq\DataTransformer\Factory\TransformerFactory;

final class DynamicDataMapper implements DataMapper
{
    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var TransformerFactory
     */
    private $transformerFactory;

    public function __construct(Builder $builder, TransformerFactory $transformerFactory)
    {
        $this->builder = $builder;
        $this->transformerFactory = $transformerFactory;
    }

    /**
     * @inheritdoc
     */
    public function map(object $source, $target, ?Schema $schema = null, array $options = []): object
    {
        if (!\is_object($target) && !\is_string($target)) {
            throw new InvalidArgumentException(\sprintf('The $target must be an object or string representing a classname.'));
        }

        $this->loadSourceIfProxy($source);

        // TODO resolve given options.
        // TODO make this recursive.
        $sourceReflection = new \ReflectionClass($source);
        $targetReflection = new \ReflectionClass($target);
        $target = \is_object($target) ? $target : $targetReflection->newInstanceWithoutConstructor();
        $schema = $schema ?? $this->builder->build($source, $target);
        $this->validateSchema($schema, $source, $target);
        foreach ($schema->getProperties() as $property) {
            $targetReflectionProperty = $targetReflection->getProperty($property->getTarget());
            $targetReflectionProperty->setAccessible(true);

            $sourceReflectionProperty = $sourceReflection->getProperty($property->getSource());
            $sourceReflectionProperty->setAccessible(true);
            $value = $sourceReflectionProperty->getValue($source);
            $transformer = $this->transformerFactory->getTransformerByType($property->getType());
            // TODO use $options to be able to suppress transform exceptions.
            $targetReflectionProperty->setValue($target, $transformer->transform($value, $property->getType()));
        }

        return $target;
    }

    private function resolveOptions(array $options): void
    {
        // TODO think of more options to support.

        // TODO option to ignore transformation exceptions, and set the value on null instead.
        // TODO option to set value on null if missing or error.
        // TODO option to override existing data. If it is already set, don't override.

    }

    private function validateSchema(Schema $schema, object $source, object $target): void
    {
        if ($schema->getSourceClass() !== \get_class($source) || $schema->getTargetClass() !== \get_class($target)) {
            throw new InvalidArgumentException(\sprintf('The schema does not match the source and target. Did you use the correct schema?'));
        }
    }

    private function loadSourceIfProxy(object $source): void
    {
        $proxyClass = 'Doctrine\ORM\Proxy\Proxy';
        if ($source instanceof $proxyClass) {
            $source->__load();
        }
    }
}
