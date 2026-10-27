<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Form;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Le picker Ibexa échange des timestamps et renvoie un DateTime en UTC.
 * Les dates sont stockées dans le fuseau PHP : on y ramène la valeur saisie.
 */
class UserTimezoneAwareDateTimeTransformer implements DataTransformerInterface
{
    public function transform($value): mixed
    {
        return $value;
    }

    public function reverseTransform($value): mixed
    {
        if (!$value instanceof \DateTimeInterface) {
            return $value;
        }

        return \DateTime::createFromInterface($value)->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    }
}
