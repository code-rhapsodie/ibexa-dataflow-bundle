<?php

namespace CodeRhapsodie\IbexaDataflowBundle\Core\FieldComparator;

use Ibexa\Contracts\Core\FieldType\Value;

class BillingAddressComparator extends AbstractFieldComparator
{

    /**
     * @param \Ibexa\FieldTypeAddress\FieldType\Value $currentValue
     * @param \Ibexa\FieldTypeAddress\FieldType\Value $newValue
     */
    protected function compareValues(Value $currentValue, Value $newValue): bool
    {
        return empty(array_diff_assoc($currentValue->fields, $newValue->fields)) && $currentValue->name === $newValue->name && $currentValue->country === $newValue->country;
    }
}
