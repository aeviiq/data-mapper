<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper\Schema\Property;

interface PropertyInterface
{
    public function getSource(): string;

    public function getTarget(): string;

    /**
     * @return string The native data type or class this property should become.
     *                e.g.: string, int, DateTime, Enum.
     */
    public function getType(): string;
}
