<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Core\Field;

use CodeRhapsodie\IbexaDataflowBundle\Exception\UnknownFieldException;
use CodeRhapsodie\IbexaDataflowBundle\Exception\UnsupportedFieldTypeException;
use Ibexa\Contracts\Core\FieldType\Value;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentStruct;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;

class ContentStructFieldFiller implements ContentStructFieldFillerInterface
{
    /**
     * ContentStructFieldFiller constructor.
     */
    public function __construct(
        /** @var FieldValueCreatorInterface[] */
        private readonly iterable $fieldValueCreators,
    ) {
    }

    /**
     * @throws UnknownFieldException
     * @throws UnsupportedFieldTypeException
     */
    public function fillFields(ContentType $contentType, ContentStruct $contentStruct, array $fieldHashes): void
    {
        foreach ($fieldHashes as $identifier => $hash) {
            $fieldDef = $contentType->getFieldDefinition($identifier);
            if ($fieldDef === null) {
                throw UnknownFieldException::create($identifier, $contentType->identifier);
            }

            $contentStruct->setField(
                $identifier,
                $this->createFieldValue($fieldDef->fieldTypeIdentifier, $hash)
            );
        }
    }

    /**
     * @throws UnsupportedFieldTypeException
     */
    private function createFieldValue(string $fieldTypeIdentifier, $hash): Value
    {
        foreach ($this->fieldValueCreators as $creator) {
            if ($creator->supports($fieldTypeIdentifier)) {
                return $creator->createValue($fieldTypeIdentifier, $hash);
            }
        }

        throw UnsupportedFieldTypeException::create($fieldTypeIdentifier);
    }
}
