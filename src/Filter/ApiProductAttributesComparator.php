<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Filter;

use CodeRhapsodie\IbexaDataflowBundle\Model\ProductUpdateStructure;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\ProductCatalog\ProductServiceInterface;
use Ibexa\Contracts\ProductCatalog\Values\AttributeInterface;

readonly class ApiProductAttributesComparator implements ProductAttributesComparatorInterface
{
    /**
     * @param iterable<ProductAttributeValueComparatorInterface> $valueComparators first supporting comparator wins
     */
    public function __construct(
        private ProductServiceInterface $productService,
        private iterable $valueComparators = [],
        private ProductAttributeValueComparatorInterface $fallback = new ScalarAttributeValueComparator(),
    ) {
    }

    public function hasModifiedAttributes(ProductUpdateStructure $data): bool
    {
        $expected = $data->getAttributes();
        if ($expected === []) {
            return false;
        }

        try {
            $product = $this->productService->getProduct($data->getCode());
        } catch (NotFoundException) {
            return true;
        }

        $stored = [];
        /** @var AttributeInterface $attribute */
        foreach ($product->getAttributes() as $attribute) {
            $stored[$attribute->getIdentifier()] = [
                $attribute->getValue(),
                $attribute->getAttributeDefinition()->getType()->getIdentifier(),
            ];
        }

        foreach ($expected as $identifier => $value) {
            if (($stored[$identifier][0] ?? null) === null) {
                return true;
            }

            [$storedValue, $type] = $stored[$identifier];
            if (!$this->comparatorFor($type)->isSame($storedValue, $value)) {
                return true;
            }
        }

        return false;
    }

    private function comparatorFor(string $type): ProductAttributeValueComparatorInterface
    {
        foreach ($this->valueComparators as $comparator) {
            if ($comparator->supports($type)) {
                return $comparator;
            }
        }

        return $this->fallback;
    }
}
