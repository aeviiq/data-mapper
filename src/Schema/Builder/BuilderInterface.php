<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper\Schema\Builder;

use Aeviiq\DataMapper\Schema\SchemaInterface;

interface BuilderInterface
{
    /**
     * TODO description
     */
    public function build(object $source, object $target): SchemaInterface;
}
