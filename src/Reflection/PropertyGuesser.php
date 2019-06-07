<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper\Reflection;

interface PropertyGuesser
{
    /**
     * TODO decide which of the algoritms to use.
     * levenshtein, SMG, jaro, similar, etc.
     * TODO take type into account.
     *
     * TODO description
     */
    public function guess(ReflectionPropertyCollection $properties, string $property): ?string;
}
