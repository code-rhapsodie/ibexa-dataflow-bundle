<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Core\FieldComparator;

use Ibexa\Contracts\Core\FieldType\Value;

class SeoComparator extends AbstractFieldComparator
{

    /**
     * @param \Ibexa\Seo\FieldType\SeoValue $currentValue
     * @param \Ibexa\Seo\FieldType\SeoValue $newValue
     * @return bool
     */
    protected function compareValues(Value $currentValue, Value $newValue): bool
    {
        $current = $currentValue->getSeoTypesValue()?->getSeoTypesValues();
        $new = $newValue->getSeoTypesValue()?->getSeoTypesValues();

        return \count($current) === \count($new)
            && empty(array_diff_assoc($current, $new));
    }
}