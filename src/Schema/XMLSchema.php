<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper\Schema;

use Aeviiq\DataMapper\Schema\Property\DataProperty;
use Aeviiq\DataMapper\Schema\Property\PropertyCollection;

final class XMLSchema implements SchemaInterface
{
    /**
     * @var string
     */
    private $sourceClass;

    /**
     * @var string
     */
    private $targetClass;

    /**
     * @var PropertyCollection
     */
    private $properties;

    public function __construct(\SimpleXMLElement $XMLElement)
    {
        $this->initialize($XMLElement);
    }

    public function getSourceClass(): string
    {
        return $this->sourceClass;
    }

    public function getTargetClass(): string
    {
        return $this->targetClass;
    }

    public function getProperties(): PropertyCollection
    {
        return $this->properties;
    }

    private function initialize(\SimpleXMLElement $XMLElement): void
    {
        $this->validateXML($XMLElement);
        $this->sourceClass = (string)$XMLElement->xpath('source')[0];
        $this->targetClass = (string)$XMLElement->xpath('target')[0];
        $this->properties = new PropertyCollection();
        foreach ($XMLElement->xpath('properties/property') as $property) {
            $attr = $property->attributes();
            $this->properties->add(new DataProperty((string)$attr->source, (string)$attr->target, (string)$attr->type));
        }
    }

    private function validateXML(\SimpleXMLElement $XMLElement): void
    {
        // TODO validate xml and throw exception on invalid input.
    }
}
