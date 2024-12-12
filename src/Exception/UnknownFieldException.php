<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Exception;

class UnknownFieldException extends \Exception
{
    public static function create(string $fieldIdentifier, string $contentTypeIdentifier): self
    {
        return new self(sprintf(
            'Unknown field %s in content type %s',
            $fieldIdentifier,
            $contentTypeIdentifier
        ));
    }
}
class_alias(UnknownFieldException::class, 'CodeRhapsodie\EzDataflowBundle\Exception\UnknownFieldException');
