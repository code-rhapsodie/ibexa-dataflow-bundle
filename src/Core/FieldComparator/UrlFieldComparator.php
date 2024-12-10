<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Core\FieldComparator;

use Ibexa\Contracts\Core\FieldType\Value;

class UrlFieldComparator extends AbstractFieldComparator
{
    protected function compareValues(Value $currentValue, Value $newValue): bool
    {
        return $currentValue->link === $newValue->link && $currentValue->text === $newValue->text;
    }
}
class_alias(UrlFieldComparator::class, 'CodeRhapsodie\EzDataflowBundle\Core\FieldComparator\UrlFieldComparator');
