<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Text input with customized display for selecting dataflow frequency.
 */
class FrequencyType extends AbstractType
{
    #[\Override]
    public function getParent(): string
    {
        return TextType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'coderhapsodie_port_frequency';
    }
}
