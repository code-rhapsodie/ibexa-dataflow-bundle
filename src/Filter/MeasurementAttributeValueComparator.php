<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Filter;

/**
 * Handles the value objects of ibexa/measurement (simple values and ranges) by duck typing,
 * so the bundle does not depend on that package.
 */
class MeasurementAttributeValueComparator implements ProductAttributeValueComparatorInterface
{
    public function supports(string $attributeType): bool
    {
        return $attributeType === 'measurement';
    }

    public function isSame(mixed $stored, mixed $expected): bool
    {
        if (!\is_object($stored) || !\is_object($expected)) {
            return false;
        }

        if ($stored::class === $expected::class && method_exists($stored, 'equals')) {
            return (bool) $stored->equals($expected);
        }

        $storedParts = $this->normalize($stored);

        return $storedParts !== null && $storedParts === $this->normalize($expected);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalize(object $value): ?array
    {
        $parts = [];
        foreach (['getMinValue', 'getMaxValue', 'getValue'] as $method) {
            if (method_exists($value, $method)) {
                $parts[$method] = $this->number($value->$method());
            }
        }

        if ($parts === [] || !method_exists($value, 'getUnit')) {
            return null;
        }

        $unit = $value->getUnit();
        $parts['unit'] = \is_object($unit) && method_exists($unit, 'getIdentifier') ? $unit->getIdentifier() : $unit;

        return $parts;
    }

    private function number(mixed $value): mixed
    {
        return is_numeric($value) ? (float) $value : $value;
    }
}
