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
    public function getParent()
    {
        return TextType::class;
    }

    public function getBlockPrefix()
    {
        return 'coderhapsodie_port_frequency';
    }
}
class_alias(FrequencyType::class, 'CodeRhapsodie\EzDataflowBundle\Form\FrequencyType');
