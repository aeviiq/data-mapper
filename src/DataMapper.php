<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper;

use Aeviiq\DataMapper\Schema\Schema;

interface DataMapper
{
    /**
     * TODO description
     *
     * @param string|object $target
     *
     * @return object The mapped target.
     */
    public function map(object $source, $target, ?Schema $schema = null, array $options = []): object;
}
