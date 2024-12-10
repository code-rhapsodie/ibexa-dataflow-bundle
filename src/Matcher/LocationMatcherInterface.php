<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Matcher;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;

interface LocationMatcherInterface
{
    /**
     * @param mixed $valueToMatch
     */
    public function matchLocation($valueToMatch): Location;
}
class_alias(LocationMatcherInterface::class, 'CodeRhapsodie\EzDataflowBundle\Matcher\LocationMatcherInterface');
