<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Filter;

/**
 * Default comparator: checkbox, integer, float, color, selection, symbol...
 */
class ScalarAttributeValueComparator implements ProductAttributeValueComparatorInterface
{
    public function supports(string $attributeType): bool
    {
        return true;
    }


    public function isSame(mixed $stored, mixed $expected): bool
    {
        if (!\is_scalar($stored) || !\is_scalar($expected)) {
            return false;
        }

        if (\is_bool($stored) || \is_bool($expected)) {
            return (bool) $stored === (bool) $expected;
        }

        if (is_numeric($stored) && is_numeric($expected)) {
            return (float) $stored === (float) $expected;
        }

        return (string) $stored === (string) $expected;
    }
}
