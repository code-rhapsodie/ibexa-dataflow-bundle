<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Model;

abstract class ContentStructure
{
    protected ?string $remoteId = null;

    protected string $languageCode;

    protected array $fields;

    public function getRemoteId(): ?string
    {
        return $this->remoteId;
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    public function getFields(): array
    {
        return $this->fields;
    }
}
