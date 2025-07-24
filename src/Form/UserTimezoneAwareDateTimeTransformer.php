<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Form;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\UserPreferenceService;
use Symfony\Component\Form\DataTransformerInterface;

class UserTimezoneAwareDateTimeTransformer implements DataTransformerInterface
{
    public function __construct(private readonly UserPreferenceService $userPreferenceService)
    {
    }

    public function transform($value): mixed
    {
        if (!$value instanceof \DateTimeInterface) {
            return $value;
        }

        return (new \DateTime('now', $this->userTimezone()))->setTimestamp($value->getTimestamp());
    }

    public function reverseTransform($value): mixed
    {
        if (!$value instanceof \DateTimeInterface) {
            return $value;
        }

        $dateTimeWithUserTimeZone = new \DateTime($value->format('Y-m-d H:i:s'), $this->userTimezone());

        return (new \DateTime())->setTimestamp($dateTimeWithUserTimeZone->getTimestamp());
    }

    private function userTimezone(): \DateTimeZone
    {
        try {
            $tz = $this->userPreferenceService->getUserPreference('timezone')->value ?? 'UTC';
        } catch (NotFoundException) {
            $tz = 'UTC';
        }

        return new \DateTimeZone($tz);
    }
}
