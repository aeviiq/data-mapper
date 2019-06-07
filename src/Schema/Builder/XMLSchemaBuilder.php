<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper\Schema\Builder;

use Aeviiq\DataMapper\Reflection\PropertyGuesser;
use Aeviiq\DataMapper\Reflection\ReflectionPropertyCollection;
use Aeviiq\DataMapper\Schema\Schema;
use Aeviiq\DataMapper\Schema\XMLSchema;

final class XMLSchemaBuilder implements Builder
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

    public function build(object $source, object $target): Schema
    {
        $xml = new \SimpleXMLElement(\sprintf(static::$template, \get_class($source), \get_class($target)));
        $properties = $xml->addChild('properties');
        $sourceReflection = new \ReflectionClass($source);
        $targetReflection = new \ReflectionClass($target);
        $sourceReflectionProperties = new ReflectionPropertyCollection($sourceReflection->getProperties());
        foreach ($targetReflection->getProperties() as $targetReflectionProperty) {
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
