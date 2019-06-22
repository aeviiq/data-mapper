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

    /**
     * @var string
     */
    private $type;

    public function __construct(string $source, string $target, string $type)
    {
        $this->source = $source;
        $this->target = $target;
        $this->type = $type;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
