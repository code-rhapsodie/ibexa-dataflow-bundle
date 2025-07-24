<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Core\Field;

use Ibexa\Contracts\Core\FieldType\Value;
use Ibexa\Contracts\Core\Repository\FieldTypeService;

class DefaultFieldValueCreator implements FieldValueCreatorInterface
{
    public function __construct(private readonly FieldTypeService $fieldTypeService)
    {
    }

    public function supports(string $fieldTypeIdentifier): bool
    {
        return true;
    }

    public function createValue(string $fieldTypeIdentifier, $hash): Value
    {
        return $this->fieldTypeService->getFieldType($fieldTypeIdentifier)->fromHash($hash);
    }
}
