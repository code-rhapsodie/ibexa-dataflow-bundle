<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Core\FieldComparator;

use Ibexa\Contracts\Core\FieldType\Value;

class ProductSpecificationComparator extends AbstractFieldComparator
{
    protected function compareValues(Value $currentValue, Value $newValue): bool
    {
        $currentAttributes = $currentValue->getAttributes();
        $newAttributes = $newValue->getAttributes();

        if ($newValue->isCodeChanged() || $currentValue->isVirtual() !== $newValue->isVirtual() || \count($currentAttributes) !== \count($newAttributes)) {
            return false;
        }

        sort($currentAttributes);
        sort($newAttributes);

        return $currentAttributes === $newAttributes;
    }
}