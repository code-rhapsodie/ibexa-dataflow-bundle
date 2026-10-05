<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Filter;

/**
 * Compares the stored value of a product attribute with the value to import, for one attribute type.
 *
 * Register implementations with the "coderhapsodie.ibexa_dataflow.product_attribute_value_comparator" tag.
 */
interface ProductAttributeValueComparatorInterface
{
    /**
     * @param string $attributeType identifier of the attribute type (checkbox, datetime, measurement...)
     */
    public function supports(string $attributeType): bool;

    public function isSame(mixed $stored, mixed $expected): bool;
}
