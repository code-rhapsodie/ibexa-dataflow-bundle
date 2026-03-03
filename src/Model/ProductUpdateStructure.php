<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Model;

class ProductUpdateStructure extends ProductStructure
{
    protected bool $updateContent = true;

    protected bool $updateStock = true;

    public function __construct(string $code, array $fields, string $languageCode, int $stock = 0, array $attributes = [])
    {
        $this->code = $code;
        $this->fields = $fields;
        $this->languageCode = $languageCode;
        $this->stock = $stock;
        $this->attributes = $attributes;
    }

    public function isUpdateContent(): bool
    {
        return $this->updateContent;
    }

    public function isUpdateStock(): bool
    {
        return $this->updateStock;
    }

    public function setUpdateContent(bool $updateContent): void
    {
        $this->updateContent = $updateContent;
    }

    public function setUpdateStock(bool $updateStock): void
    {
        $this->updateStock = $updateStock;
    }
}
