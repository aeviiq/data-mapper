<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper\Schema\Property;

use Aeviiq\Collection\ObjectCollection;

/**
 * @method \ArrayIterator|PropertyInterface[] getIterator
 * @method PropertyInterface|null first
 * @method PropertyInterface|null last
 */
final class PropertyCollection extends ObjectCollection
{
    protected function allowedInstance(): string
    {
        return PropertyInterface::class;
    }
}
