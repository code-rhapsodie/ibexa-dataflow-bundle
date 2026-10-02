<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Matcher;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;

interface LocationMatcherInterface
{
    public function matchLocation($valueToMatch): Location;
}
