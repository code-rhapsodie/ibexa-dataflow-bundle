<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Model;

class ProductCreateStructure extends ProductStructure
{
    public function __construct(string $code, array $fields, string $languageCode)
    {
        $this->code = $code;
        $this->fields = $fields;
        $this->languageCode = $languageCode;
    }
}
