<?php

namespace CodeRhapsodie\IbexaDataflowBundle\Model;

abstract class ProductStructure
{
    protected string $code;

    protected array $fields;

    protected string $languageCode;

    protected int $stock;


    public function getCode(): string
    {
        return $this->code;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    public function getStock(): int
    {
        return $this->stock;
    }
}
