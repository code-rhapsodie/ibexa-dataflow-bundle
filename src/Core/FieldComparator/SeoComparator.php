<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Core\FieldComparator;

use Ibexa\Contracts\Core\FieldType\Value;
use Ibexa\Seo\Value\SeoTypeValue;

class SeoComparator extends AbstractFieldComparator
{

    /**
     * @param \Ibexa\Seo\FieldType\SeoValue $currentValue
     * @param \Ibexa\Seo\FieldType\SeoValue $newValue
     * @return bool
     */
    protected function compareValues(Value $currentValue, Value $newValue): bool
    {
        $currentSeoTypeValues = $currentValue->getSeoTypesValue()?->getSeoTypesValues() ?: [];
        $newSeoTypeValues = $newValue->getSeoTypesValue()?->getSeoTypesValues() ?: [];


        if (\count($currentSeoTypeValues) !== \count($newSeoTypeValues)){
            return false;
        }

        return array_all(
            $currentSeoTypeValues,
            function (SeoTypeValue $oldItem, string $key) use ($newSeoTypeValues): bool {
                if (!array_key_exists($key, $newSeoTypeValues)) {
                    return false;
                }

                $newItem = $newSeoTypeValues[$key];

                return $oldItem->getType() === $newItem->getType()
                    && $oldItem->getFields() === $newItem->getFields();
            }
        );
    }
}