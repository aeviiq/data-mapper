<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper;

use Aeviiq\DataMapper\Exception\InvalidArgumentException;
use Aeviiq\DataMapper\Schema\Builder\BuilderInterface;
use Aeviiq\DataMapper\Schema\Property\PropertyInterface;
use Aeviiq\DataMapper\Schema\SchemaInterface;
use Aeviiq\DataTransformer\Exception\ExceptionInterface;
use Aeviiq\DataTransformer\Factory\TransformerFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class DynamicDataMapper implements DataMapperInterface
{
    /**
     * @var mixed[]
     */
    private static $defaultOptions = [
        'suppress_transformation_exceptions' => false,
        'override_existing_values' => true,
        'memorize_schema' => true,
    ];

    /**
     * @var string[]
     */
    private static $memorizedSchemas = [];

    /**
     * @var BuilderInterface
     */
    private $builder;

    /**
     * @var TransformerFactory
     */
    private $transformerFactory;

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    public function __construct(BuilderInterface $builder, TransformerFactory $transformerFactory, OptionsResolver $optionsResolver)
    {
        $this->builder = $builder;
        $this->transformerFactory = $transformerFactory;
        $this->optionsResolver = $optionsResolver;
        $this->configureOptions();
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @inheritdoc
     */
    public function map(object $source, $target, ?SchemaInterface $schema = null, array $options = []): object
    {
        if (!\is_object($target) && !\is_string($target)) {
            throw new InvalidArgumentException(\sprintf('The $target must be an object or a string representing an existing class.'));
        }

        // TODO make this recursive.

        $this->loadSourceIfProxy($source);
        // TODO implement the resolved options.
//        $options = $this->optionsResolver->resolve($options);
        $target = $this->resolveTarget($target);
        $schema = $this->resolveSchema($schema, $source, $target);
        foreach ($schema->getProperties() as $property) {
            $this->mapProperty($source, $target, $property);
        }

        return $target;
    }

    private function mapProperty(object $source, object $target, PropertyInterface $property): void
    {
        // TODO implement 'override_existing_values'
        $this->propertyAccessor->setValue($target, $property->getTarget(), $this->getTransformedValue($source, $property));
    }

    private function getTransformedValue(object $source, PropertyInterface $property /*, bool $suppressException */)
    {
        try {
            $transformer = $this->transformerFactory->getTransformerByType($property->getType());
            return $transformer->transform($this->propertyAccessor->getValue($source, $property->getSource()));
        } catch (ExceptionInterface $e) {
            // TODO implement 'suppress_transformation_exceptions'
//            if (!$suppressException) {
//                throw $e;
//            }

            return null;
        }
    }

    private function resolveSchema(?SchemaInterface $schema, object $source, object $target): SchemaInterface
    {
        $schema = $schema ?? $this->createSchema($source, $target, true);
        $this->validateSchema($schema, $source, $target);

        return $schema;
    }

    /**
     * @param object|string $target
     */
    private function resolveTarget($target): object
    {
        if (\is_object($target)) {
            return $target;
        }

        if (\is_string($target) && \class_exists($target)) {
            return (new \ReflectionClass($target))->newInstanceWithoutConstructor();
        }

        throw new InvalidArgumentException(\sprintf('The target must be an object or a string representing an existing class.'));
    }

    private function createSchema(object $source, object $target, bool $memorize): SchemaInterface
    {
        $hash = \md5(\get_class($source) . \get_class($target));
        if (isset(static::$memorizedSchemas[$hash])) {
            return static::$memorizedSchemas[$hash];
        }

        $schema = $this->builder->build($source, $target);
        if ($memorize) {
            static::$memorizedSchemas[$hash] = $schema;
        }

        return $schema;
    }

    private function configureOptions(): void
    {
        // TODO think of more options to support.
        // TODO option to set value on null if missing or error.
        $this->optionsResolver
            ->setRequired('suppress_transformation_exceptions')
            ->setAllowedTypes('suppress_transformation_exceptions', 'bool');

        $this->optionsResolver
            ->setRequired('override_existing_values')
            ->setAllowedTypes('override_existing_values', 'bool');

        $this->optionsResolver
            ->setRequired('memorize_schema')
            ->setAllowedTypes('memorize_schema', 'bool');

        $this->optionsResolver->setDefaults(static::$defaultOptions);
    }

    private function validateSchema(SchemaInterface $schema, object $source, object $target): void
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
