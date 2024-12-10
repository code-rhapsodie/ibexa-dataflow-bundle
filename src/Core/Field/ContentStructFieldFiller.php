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
    /** @var FieldValueCreatorInterface[] */
    private $fieldValueCreators;

    /**
     * ContentStructFieldFiller constructor.
     */
    public function __construct(iterable $fieldValueCreators)
    {
        $this->fieldValueCreators = $fieldValueCreators;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \CodeRhapsodie\IbexaDataflowBundle\Exception\UnknownFieldException
     * @throws \CodeRhapsodie\IbexaDataflowBundle\Exception\UnsupportedFieldTypeException
     */
    public function fillFields(ContentType $contentType, ContentStruct $contentStruct, array $fieldHashes): void
    {
        foreach ($fieldHashes as $identifier => $hash) {
            $fieldDef = $contentType->getFieldDefinition($identifier);
            if (null === $fieldDef) {
                throw UnknownFieldException::create($identifier, $contentType->identifier);
            }

            $contentStruct->setField(
                $identifier,
                $this->createFieldValue($fieldDef->fieldTypeIdentifier, $hash)
            );
        }
    }

    /**
     * @param mixed $hash
     *
     * @throws \CodeRhapsodie\IbexaDataflowBundle\Exception\UnsupportedFieldTypeException
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
class_alias(ContentStructFieldFiller::class, 'CodeRhapsodie\EzDataflowBundle\Core\Field\ContentStructFieldFiller');
