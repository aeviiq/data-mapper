<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper\Schema\Builder;

use Aeviiq\DataMapper\Reflection\PropertyGuesser;
use Aeviiq\DataMapper\Reflection\ReflectionPropertyCollection;
use Aeviiq\DataMapper\Schema\Schema;
use Aeviiq\DataMapper\Schema\XMLSchema;

final class XMLSchemaBuilder implements Builder
{
    /**
     * @var PropertyGuesser
     */
    private $propertyGuesser;

    public function __construct(PropertyGuesser $propertyGuesser)
    {
        $this->propertyGuesser = $propertyGuesser;
    }

    public function build(object $source, object $target): Schema
    {
        $xml = new \SimpleXMLElement('<schema><source>' . \get_class($source) . '</source><target>' . \get_class($target) . '</target></schema>');
        $properties = $xml->addChild('properties');
        $sourceReflection = new \ReflectionClass($source);
        $targetReflection = new \ReflectionClass($target);
        $targetReflectionProperties = $targetReflection->getProperties();
        $sourceReflectionProperties = new ReflectionPropertyCollection($sourceReflection->getProperties());
        foreach ($targetReflectionProperties as $targetReflectionProperty) {
            $targetPropertyName = $targetReflectionProperty->getName();
            if ($sourceReflection->hasProperty($targetPropertyName)) {
                // Remove the property from the collection so we do not include these in the guessing.
                $sourceReflectionProperties->removeByName($targetPropertyName);
                $this->addPropertyToXML($properties, $targetPropertyName, $targetPropertyName);
                continue;
            }

            $guessed = $this->propertyGuesser->guess($sourceReflectionProperties, $targetPropertyName);
            if (null !== $guessed && '' !== $guessed) {
                $this->addPropertyToXML($properties, $guessed, $targetPropertyName);
            }
        }

        return new XMLSchema($xml);
    }

    private function addPropertyToXML(\SimpleXMLElement $xml, string $sourceProperty, string $targetProperty): void
    {
        $property = $xml->addChild('property', '');
        $property->addAttribute('source', $sourceProperty);
        $property->addAttribute('target', $targetProperty);
    }
}
