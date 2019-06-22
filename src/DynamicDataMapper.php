<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper;

use Aeviiq\DataMapper\Exception\InvalidArgumentException;
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
        if (!\is_object($target) && !\is_string($target)) {
            throw new InvalidArgumentException(\sprintf('The $target must be an object or string representing a classname.'));
        }

        $this->loadSourceIfProxy($source);
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
            // TODO use $options to be able to suppress transform exceptions.
            $targetReflectionProperty->setValue($target, $transformer->transform($value, $propertyType));
        }

        return $target;
    }

    private function loadSourceIfProxy(object $source): void
    {
        $proxyClass = 'Doctrine\ORM\Proxy\Proxy';
        if ($source instanceof $proxyClass) {
            $source->__load();
        }
    }
}
