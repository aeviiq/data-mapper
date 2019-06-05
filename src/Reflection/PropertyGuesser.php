<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper\Reflection;

interface PropertyGuesser
{
    /**
     * TODO decide which of the algoritms to use.
     * levenshtein, SMG, jaro, similar, etc.
     *
     * TODO description
     */
    public function guess(\ReflectionClass $reflectionClass, string $property): ?string;
}
