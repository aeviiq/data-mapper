<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper\Reflection;

use Aeviiq\Collection\ObjectCollection;

/**
 * @method \ArrayIterator|\ReflectionProperty[] getIterator
 * @method \ReflectionProperty|null first
 * @method \ReflectionProperty|null last
 */
final class ReflectionPropertyCollection extends ObjectCollection
{
    public function removeByName(string $name): void
    {
        foreach ($this as $element) {
            if ($element->getName() === $name) {
                $this->removeElement($element);
                break;
            }
        }
    }

    protected function allowedInstance(): string
    {
        return \ReflectionProperty::class;
    }
}
