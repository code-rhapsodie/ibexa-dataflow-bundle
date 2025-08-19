<?php

namespace CodeRhapsodie\IbexaDataflowBundle\Core\FieldComparator;

use Ibexa\Contracts\Core\FieldType\Value;

class MapLocationComparator extends AbstractFieldComparator
{

    /**
     * @param \Ibexa\Core\FieldType\MapLocation\Value $currentValue
     * @param \Ibexa\Core\FieldType\MapLocation\Value $newValue
     */
    protected function compareValues(Value $currentValue, Value $newValue): bool
    {
        return $currentValue->address === $newValue->address && $currentValue->latitude === $newValue->latitude && $currentValue->longitude === $newValue->longitude;
    }
}