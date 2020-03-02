<?php declare(strict_types=1);

namespace Aeviiq\DataMapper;

use Aeviiq\DataMapper\Exception\InvalidArgumentException;
use Aeviiq\DataMapper\Exception\LogicException;
use Aeviiq\DataMapper\Exception\RuntimeException;
use Aeviiq\DataMapper\Exception\UnexpectedValueException;
use Aeviiq\SafeCast\SafeCast;

final class DynamicDataMapper
{
    /**
     * Current implementation is mainly build to map value objects.
     * e.g. A form model with nullable values to a strict read-only data object.
     *
     * When strict mode is enabled, most native types will be casted to their expected type,
     * using the SafeCast component to ensure there are no unwanted side effects that regular type casting could have.
     *
     * @param string|object $target
     *
     * @return object The target object with the mapped values
     *
     * @throws LogicException When the target does not have the same properties as the source.
     * @throws RuntimeException When any reflection exception occurs while mapping.
     */
    public static function map(object $source, $target, bool $strict = true): object
    {
        try {
            $sourceReflection = new \ReflectionObject($source);
            $targetReflection = self::createReflectionClass($target);
            $result = $targetReflection->newInstanceWithoutConstructor();
            foreach ($sourceReflection->getProperties() as $sourceReflectionProperty) {
                $sourceReflectionProperty->setAccessible(true);
                $sourceValue = $sourceReflectionProperty->getValue($source);
                $sourceReflectionPropertyName = $sourceReflectionProperty->getName();
                if (!$targetReflection->hasProperty($sourceReflectionPropertyName)) {
                    throw new LogicException(
                        \sprintf(
                            'Property "%s" does not exist in class "%s". Ensure the source and target have the same property names.',
                            $sourceReflectionPropertyName,
                            $targetReflection->getName()
                        )
                    );
                }

                if ($strict) {
                    // Could be replaced with property reflection in PHP >= 7.4
                    $reflectionType = self::getReflectionType(self::getReflectionMethod($sourceReflectionPropertyName, $targetReflection));
                    if (null === $sourceValue && !$reflectionType->allowsNull()) {
                        throw new LogicException(
                            \sprintf(
                                'Property "%s" of source "%s" cannot be null.',
                                $sourceReflectionPropertyName,
                                $sourceReflection->getName()
                            )
                        );
                    }

                    $sourceValue = self::cast($reflectionType, $sourceValue);
                }

                $targetProperty = $targetReflection->getProperty($sourceReflectionPropertyName);
                $targetProperty->setAccessible(true);
                $targetProperty->setValue($result, $sourceValue);
            }

            return $result;
        } catch (\ReflectionException $e) {
            throw new RuntimeException(
                \sprintf('Unable to map object "%s" to "%s". "%s"',
                    \get_class($source),
                    \is_object($target) ? \get_class($target) : $target,
                    $e->getMessage()
                ), 0, $e);
        }
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws UnexpectedValueException When the value is an unsupported type.
     */
    private static function cast(\ReflectionType $reflectionType, $value)
    {
        switch (true) {
            case (null === $value):
            case (\is_resource($value)):
            case (\is_array($value)):
            case (\is_object($value)):
                return $value;
            case ('bool' === (string)$reflectionType):
                return SafeCast::toBool($value);
            case ('string' === (string)$reflectionType):
                return SafeCast::toString($value);
            case ('int' === (string)$reflectionType):
                return SafeCast::toInt($value);
            case ('float' === (string)$reflectionType):
                return SafeCast::toFloat($value);
            default:
                throw new UnexpectedValueException('Unsupported value type.');
        }
    }

    private static function getReflectionMethod(string $propertyName, \ReflectionClass $targetReflection): \ReflectionMethod
    {
        if ($targetReflection->hasMethod($methodName = \sprintf('get%s', $propertyName))) {
            return $targetReflection->getMethod($methodName);
        }

        if ($targetReflection->hasMethod($methodName = \sprintf('is%s', $propertyName))) {
            return $targetReflection->getMethod($methodName);
        }

        if ($targetReflection->hasMethod($methodName = \sprintf('has%s', $propertyName))) {
            return $targetReflection->getMethod($methodName);
        }

        throw new LogicException(
            \sprintf(
                'In strict mode, a read-only object must have atleast a public get|has|is method for each of it\'s properties. No method found for property "%s".',
                $propertyName
            )
        );
    }

    private static function getReflectionType(\ReflectionMethod $reflectionMethod): \ReflectionType
    {
        if (null !== $returnType = $reflectionMethod->getReturnType()) {
            return $returnType;
        }

        throw new LogicException(\sprintf('A simple read-only object must have return types for it\'s read methods.'));
    }

    private static function createReflectionClass($target): \ReflectionClass
    {
        if (\is_string($target)) {
            if (!\class_exists($target)) {
                throw new InvalidArgumentException(\sprintf('The target must be an existing class name. "%s" does not exist.', $target));
            }

            return new \ReflectionClass($target);
        }

        if (\is_object($target)) {
            return new \ReflectionObject($target);
        }

        throw new InvalidArgumentException('The target must either be an object or a string that represents a valid class name.');
    }
}
