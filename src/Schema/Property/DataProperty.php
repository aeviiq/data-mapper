<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper\Schema\Property;

final class DataProperty implements Property
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $target;

    public function __construct(string $source, string $target)
    {
        $this->source = $source;
        $this->target = $target;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getTarget(): string
    {
        return $this->target;
    }
}
