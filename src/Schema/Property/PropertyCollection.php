<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper\Schema\Property;

use Aeviiq\Collection\ObjectCollection;

final class PropertyCollection extends ObjectCollection
{
    protected function allowedInstance(): string
    {
        return Property::class;
    }
}
