<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Core\FieldComparator;

use Ibexa\Contracts\Core\FieldType\Value;

class SimpleFieldComparator extends AbstractFieldComparator
{
    protected function compareValues(Value $currentValue, Value $newValue): bool
    {
        return (string) $currentValue === (string) $newValue;
    }
}
class_alias(SimpleFieldComparator::class, 'CodeRhapsodie\EzDataflowBundle\Core\FieldComparator\SimpleFieldComparator');
