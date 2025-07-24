<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Matcher;

use CodeRhapsodie\IbexaDataflowBundle\Exception\NoMatchFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

class LocationMatcher implements LocationMatcherInterface
{
    public function __construct(private readonly LocationService $locationService)
    {
    }

    /**
     * @param mixed $valueToMatch
     *
     * @throws \CodeRhapsodie\IbexaDataflowBundle\Exception\NoMatchFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function matchLocation($valueToMatch): Location
    {
        if ($valueToMatch instanceof Location) {
            return $valueToMatch;
        }

        try {
            if (is_int($valueToMatch)) {
                return $this->locationService->loadLocation($valueToMatch);
            }

            if (is_string($valueToMatch)) {
                return $this->locationService->loadLocationByRemoteId($valueToMatch);
            }
        } catch (NotFoundException $e) {
            throw new NoMatchFoundException('No location matched provided value', 0, $e);
        }

        throw new NoMatchFoundException('No location matched provided value');
    }
}
