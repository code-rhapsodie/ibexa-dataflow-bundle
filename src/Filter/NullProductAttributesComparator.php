<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Filter;

use CodeRhapsodie\IbexaDataflowBundle\Model\ProductUpdateStructure;

/**
 * Used when ibexa/product-catalog is not installed: attributes cannot be compared, so they never trigger an update.
 */
readonly class NullProductAttributesComparator implements ProductAttributesComparatorInterface
{
    public function hasModifiedAttributes(ProductUpdateStructure $data): bool
    {
        return false;
    }
}
