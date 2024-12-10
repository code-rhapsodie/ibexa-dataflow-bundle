<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle;

use CodeRhapsodie\IbexaDataflowBundle\DependencyInjection\CodeRhapsodieIbexaDataflowExtension;
use CodeRhapsodie\IbexaDataflowBundle\DependencyInjection\Compiler\FieldComparatorCompilerPass;
use CodeRhapsodie\IbexaDataflowBundle\Security\PolicyProvider;
use Ibexa\Bundle\Core\DependencyInjection\IbexaCoreExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CodeRhapsodieIbexaDataflowBundle extends Bundle
{
    protected $name = 'CodeRhapsodieIbexaDataflowBundle';

    public function getContainerExtension()
    {
        return new CodeRhapsodieIbexaDataflowExtension();
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new FieldComparatorCompilerPass());

        /** @var IbexaCoreExtension $ibexaExtension */
        $ibexaExtension = $container->getExtension('ibexa');
        $ibexaExtension->addPolicyProvider(new PolicyProvider());
    }
}
class_alias(CodeRhapsodieIbexaDataflowBundle::class, 'CodeRhapsodie\EzDataflowBundle\CodeRhapsodieIbexaDataflowBundle');
