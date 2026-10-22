<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Form;

use Ibexa\AdminUi\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * The Ibexa picker handles the user timezone on the client side and submits an epoch timestamp, converted to a UTC
 * \DateTime. Dates are stored in the PHP default timezone (Doctrine does not convert them), so the submitted date
 * must be brought back to this timezone.
 */
class LocalDateTimePickerType extends AbstractType
{
    #[\Override]
    public function getParent(): string
    {
        return DateTimePickerType::class;
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Model transformers are prepended: this one runs last on reverseTransform, after the parent built the \DateTime
        $builder->addModelTransformer(new CallbackTransformer(
            static fn (mixed $value): mixed => $value,
            static fn (mixed $value): mixed => $value instanceof \DateTime
                ? $value->setTimezone(new \DateTimeZone(date_default_timezone_get()))
                : $value,
        ));
    }
}
