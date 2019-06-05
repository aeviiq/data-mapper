<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper\Schema\Builder;

use Aeviiq\DataMapper\Schema\Schema;

interface Builder
{
    /**
     * TODO description
     */
    public function build(object $source, object $target): Schema;
}
