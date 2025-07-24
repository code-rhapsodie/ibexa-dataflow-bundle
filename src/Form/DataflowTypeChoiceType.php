<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Form;

use CodeRhapsodie\DataflowBundle\Registry\DataflowTypeRegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DataflowTypeChoiceType extends AbstractType
{
    public function __construct(private readonly DataflowTypeRegistryInterface $registry)
    {
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $choices = [];
        foreach ($this->registry->listDataflowTypes() as $fqcn => $dataflowType) {
            $choices[$dataflowType->getLabel()] = $fqcn;
        }

        $resolver->setDefaults([
            'choices' => $choices,
        ]);
    }
}
