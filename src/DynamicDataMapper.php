<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper;

use Aeviiq\DataMapper\Exception\InvalidArgumentException;
use Aeviiq\DataMapper\Schema\Builder\BuilderInterface;
use Aeviiq\DataMapper\Schema\Property\PropertyInterface;
use Aeviiq\DataMapper\Schema\SchemaInterface;
use Aeviiq\DataTransformer\Exception\ExceptionInterface;
use Aeviiq\DataTransformer\Factory\TransformerFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DynamicDataMapper implements DataMapperInterface
{
    private static $defaultOptions = [
        'suppress_transformation_exceptions' => false,
        'override_existing_values' => true,
    ];

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

    public function __construct(BuilderInterface $builder, TransformerFactory $transformerFactory, OptionsResolver $optionsResolver)
    {
        $this->builder = $builder;
        $this->transformerFactory = $transformerFactory;
        $this->optionsResolver = $optionsResolver;
        $this->configureOptions();
    }

    /**
     * @inheritdoc
     */
    public function map(object $source, $target, ?SchemaInterface $schema = null, array $options = []): object
    {
        if (!\is_object($target) && !\is_string($target)) {
            throw new InvalidArgumentException(\sprintf('The $target must be an object or string representing a classname.'));
        }

        $this->loadSourceIfProxy($source);
        $options = $this->optionsResolver->resolve($options);

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

            $currentTargetValue = $targetReflectionProperty->getValue($target);
            $sourceValue = $sourceReflectionProperty->getValue($source);

            if (!$options['override_existing_values'] && null !== $currentTargetValue && '' !== $currentTargetValue) {
                continue;
            }

            $targetReflectionProperty->setValue($target, $this->getTransformedValue($property, $sourceValue, $options['suppress_transformation_exceptions']));
        }

        return $target;
    }

    private function getTransformedValue(PropertyInterface $property, $value, bool $suppressException)
    {
        try {
            $transformer = $this->transformerFactory->getTransformerByType($property->getType());
            return $transformer->transform($value);
        } catch (ExceptionInterface $e) {
            if (!$suppressException) {
                throw $e;
            }

            return null;
        }
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
