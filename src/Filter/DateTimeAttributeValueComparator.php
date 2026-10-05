<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Filter;

/**
 * Values may be DateTimeInterface or any string DateTime can parse.
 */
class DateTimeAttributeValueComparator implements ProductAttributeValueComparatorInterface
{
    public function supports(string $attributeType): bool
    {
        return $attributeType === 'datetime';
    }

    public function isSame(mixed $stored, mixed $expected): bool
    {
        $storedTimestamp = $this->toTimestamp($stored);
        $expectedTimestamp = $this->toTimestamp($expected);

        return $storedTimestamp !== null && $storedTimestamp === $expectedTimestamp;
    }

    private function toTimestamp(mixed $value): ?int
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }

        if (!\is_string($value)) {
            return null;
        }

        try {
            return (new \DateTimeImmutable($value))->getTimestamp();
        } catch (\Exception) {
            return null;
        }
    }
}
