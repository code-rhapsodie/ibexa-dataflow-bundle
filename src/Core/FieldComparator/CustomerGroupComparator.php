<?php

namespace CodeRhapsodie\IbexaDataflowBundle\Core\FieldComparator;

use Ibexa\Contracts\Core\FieldType\Value;

class CustomerGroupComparator extends AbstractFieldComparator
{

    /**
     * @param \Ibexa\ProductCatalog\FieldType\CustomerGroup\Value $currentValue
     * @param \Ibexa\ProductCatalog\FieldType\CustomerGroup\Value $newValue
     */
    protected function compareValues(Value $currentValue, Value $newValue): bool
    {
        return $currentValue->getCustomerGroup()->getIdentifier() === $newValue->getCustomerGroup()->getIdentifier();
    }
}
