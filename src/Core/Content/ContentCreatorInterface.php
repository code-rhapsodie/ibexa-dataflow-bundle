<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Core\Content;

use CodeRhapsodie\IbexaDataflowBundle\Model\ContentCreateStructure;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;

interface ContentCreatorInterface
{
    public function createFromStructure(ContentCreateStructure $structure): Content;
}
class_alias(ContentCreatorInterface::class, 'CodeRhapsodie\EzDataflowBundle\Core\Content\ContentCreatorInterface');
