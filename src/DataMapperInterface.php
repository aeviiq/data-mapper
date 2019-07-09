<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper;

use Aeviiq\DataMapper\Schema\SchemaInterface;

interface DataMapperInterface
{
    /**
     * TODO description
     *
     * @param string|object $target
     *
     * @return object The mapped target.
     */
    public function map(object $source, $target, ?SchemaInterface $schema = null, array $options = []): object;
}
