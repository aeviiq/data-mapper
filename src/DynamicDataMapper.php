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

    public function map(object $source, object $target, ?Schema $schema = null, array $options = []): void
    {
        // TODO implement support for proxies.
        // TODO resolve given options.
        // TODO validate schema against source and target.

        $schema = $schema ?? $this->builder->build($source, $target);
        // TODO make this recursive.
        $sourceReflection = new \ReflectionClass($schema->getSourceClass());
        $targetReflection = new \ReflectionClass($schema->getTargetClass());
        foreach ($schema->getProperties() as $property) {
            // TODO implement map().
        }
    }
}
