<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper\Schema\Property;

interface Property
{
    public function getSource(): string;

    public function getTarget(): string;
}
