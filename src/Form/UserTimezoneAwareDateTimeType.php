<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Form;

use Ibexa\AdminUi\Form\Type\DateTimePickerType;
use Ibexa\Contracts\Core\Repository\UserPreferenceService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserTimezoneAwareDateTimeType extends AbstractType
{
    public function __construct(private readonly UserPreferenceService $userPreferenceService)
    {
    }

    #[\Override]
    public function getParent(): string
    {
        return DateTimePickerType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new UserTimezoneAwareDateTimeTransformer($this->userPreferenceService));
    }
}
