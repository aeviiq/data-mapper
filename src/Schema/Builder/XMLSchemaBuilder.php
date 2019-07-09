<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper\Schema\Builder;

use Aeviiq\DataMapper\Reflection\PropertyGuesser;
use Aeviiq\DataMapper\Reflection\ReflectionPropertyCollection;
use Aeviiq\DataMapper\Schema\SchemaInterface;
use Aeviiq\DataMapper\Schema\XMLSchema;
use Aeviiq\Reflection\ReflectionHelper;

final class XMLSchemaBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    private static $template = '<schema><source>%s</source><target>%s</target></schema>';

    /**
     * @var PropertyGuesser
     */
    private $propertyGuesser;

    public function __construct(PropertyGuesser $propertyGuesser)
    {
        $this->propertyGuesser = $propertyGuesser;
    }

    public function build(object $source, object $target): SchemaInterface
    {
        $xml = new \SimpleXMLElement(\sprintf(static::$template, \get_class($source), \get_class($target)));
        $properties = $xml->addChild('properties');
        $sourceReflection = new \ReflectionClass($source);
        $targetReflection = new \ReflectionClass($target);
        $sourceReflectionProperties = new ReflectionPropertyCollection($sourceReflection->getProperties());
        $targetReflectionProperties = new ReflectionPropertyCollection($targetReflection->getProperties());
        foreach ($targetReflectionProperties as $targetReflectionProperty) {
            $targetPropertyName = $targetReflectionProperty->getName();
            if (!$sourceReflection->hasProperty($targetPropertyName)) {
                continue;
            }

            // Remove the property from the collection so we do not include these in the guessing.
            $this->addPropertyToXML($properties, $targetPropertyName, $targetReflectionProperty);
            $sourceReflectionProperties->removeByName($targetPropertyName);
            $targetReflectionProperties->removeElement($targetReflectionProperty);
        }
        $this->buildGuessed($properties, $sourceReflectionProperties, $targetReflectionProperties);

        // TODO implement way to get just the xml output as string, either in this builder or in the schema?
        return new XMLSchema($xml);
    }

    private function buildGuessed(
        \SimpleXMLElement $XMLElement,
        ReflectionPropertyCollection $sourceReflectionProperties,
        ReflectionPropertyCollection $targetReflectionProperties
    ): void {
        foreach ($targetReflectionProperties as $targetReflectionProperty) {
            $targetPropertyName = $targetReflectionProperty->getName();
            $guessed = $this->propertyGuesser->guess($sourceReflectionProperties, $targetPropertyName);
            if (null === $guessed || '' === $guessed) {
                continue;
            }

            $this->addPropertyToXML($XMLElement, $guessed, $targetReflectionProperty);
        }
    }

    private function addPropertyToXML(\SimpleXMLElement $XMLElement, string $sourceProperty, \ReflectionProperty $targetReflectionProperty): void
    {
        $property = $XMLElement->addChild('property', '');
        $property->addAttribute('source', $sourceProperty);
        $property->addAttribute('target', $targetReflectionProperty->getName());
        $property->addAttribute('type', ReflectionHelper::getPropertyType($targetReflectionProperty));
    }
}
