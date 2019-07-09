<?php declare(strict_types = 1);

namespace Aeviiq\DataMapper\Reflection;

final class PropertyGuesser implements PropertyGuesserInterface
{
    /**
     * @var float
     */
    private $minimumMatchPercentage;

    public function __construct(float $minimumMatchPercentage = 85.0)
    {
        $this->minimumMatchPercentage = $minimumMatchPercentage;
    }

    public function guess(ReflectionPropertyCollection $properties, string $property): ?string
    {
        $percentage = 0.0;
        $result = null;
        // TODO refactor to allow for multiple algoritms
        foreach ($properties as $reflectionProperty) {
            $matchCount = \similar_text($reflectionProperty->getName(), $property, $currentPercentage);
            if ($currentPercentage < $percentage || $currentPercentage < $this->minimumMatchPercentage) {
                continue;
            }
            // TODO take match count into consideration?
            $percentage = $currentPercentage;

            $result = $reflectionProperty->getName();
        }

        return $result;
    }
}
