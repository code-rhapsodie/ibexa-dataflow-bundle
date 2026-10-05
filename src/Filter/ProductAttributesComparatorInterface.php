<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Filter;

use CodeRhapsodie\IbexaDataflowBundle\Model\ProductUpdateStructure;

interface ProductAttributesComparatorInterface
{
    public function hasModifiedAttributes(ProductUpdateStructure $data): bool;
}
