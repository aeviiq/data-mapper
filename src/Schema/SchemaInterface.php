<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper\Schema;

use Aeviiq\DataMapper\Schema\Property\PropertyCollection;

interface SchemaInterface
{
    public function getSourceClass(): string;

    public function getTargetClass(): string;

    public function getProperties(): PropertyCollection;
}
