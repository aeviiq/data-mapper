<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper;

use Aeviiq\DataMapper\Schema\Builder\Builder;
use Aeviiq\DataMapper\Schema\Schema;

final class DynamicDataMapper implements DataMapper
{
    /**
     * @var Builder
     */
    private $builder;

    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
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
            // TODO scalar normalization based on target
            $targetReflectionProperty->setValue($target, $value ?? null);
        }

        return $target;
    }
}
