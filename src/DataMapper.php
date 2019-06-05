<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper;

use Aeviiq\DataMapper\Schema\Schema;

interface DataMapper
{
    public function map(object $source, object $target, ?Schema $schema = null, array $options = []): void;
}
