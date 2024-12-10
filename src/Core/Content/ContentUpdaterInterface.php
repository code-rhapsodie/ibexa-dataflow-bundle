<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Core\Content;

use CodeRhapsodie\IbexaDataflowBundle\Model\ContentUpdateStructure;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;

interface ContentUpdaterInterface
{
    public function updateFromStructure(ContentUpdateStructure $structure): Content;
}
class_alias(ContentUpdaterInterface::class, 'CodeRhapsodie\EzDataflowBundle\Core\Content\ContentUpdaterInterface');
