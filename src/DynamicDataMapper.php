<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper;

use Aeviiq\DataMapper\Schema\Builder\Builder;
use Aeviiq\DataMapper\Schema\Schema;
use Aeviiq\DataTransformer\Factory\TransformerFactory;
use Aeviiq\DataTransformer\Reflection\Property\ReflectionPropertyHelper;

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
        // TODO ensure target is either an object or string.
        // TODO implement support for proxies.
        // TODO resolve given options.
        // TODO validate schema against source and target.

        // TODO make this recursive.
        $sourceReflection = new \ReflectionClass($source);
        $targetReflection = new \ReflectionClass($target);
        $target = \is_object($target) ? $target : $targetReflection->newInstanceWithoutConstructor();
        $schema = $schema ?? $this->builder->build($source, $target);
        foreach ($schema->getProperties() as $property) {
            $targetReflectionProperty = $targetReflection->getProperty($property->getTarget());
            $targetReflectionProperty->setAccessible(true);

            $sourceReflectionProperty = $sourceReflection->getProperty($property->getSource());
            $sourceReflectionProperty->setAccessible(true);
            $value = $sourceReflectionProperty->getValue($source);
            $propertyType = ReflectionPropertyHelper::readPropertyType($targetReflectionProperty);
            $transformer = $this->transformerFactory->getTransformerByType($propertyType);
            $targetReflectionProperty->setValue($target, $transformer->transform($value, $propertyType));
        }

        return $target;
    }
}
