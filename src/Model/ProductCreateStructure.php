<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Model;

class ProductCreateStructure extends ProductStructure
{
    public function __construct(string $code, array $fields, string $languageCode, int $stock = 0, array $attributes = [])
    {
        $this->code = $code;
        $this->fields = $fields;
        $this->languageCode = $languageCode;
        $this->stock = $stock;
        $this->attributes = $attributes;
    }
}
