<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle;

use CodeRhapsodie\IbexaDataflowBundle\DependencyInjection\CodeRhapsodieIbexaDataflowExtension;
use CodeRhapsodie\IbexaDataflowBundle\DependencyInjection\Compiler\FieldComparatorCompilerPass;
use CodeRhapsodie\IbexaDataflowBundle\Security\PolicyProvider;
use Ibexa\Bundle\Core\DependencyInjection\IbexaCoreExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CodeRhapsodieIbexaDataflowBundle extends Bundle
{
    public const string VERSION = '6.0.0';
    public const string PRODUCT_NAME = 'ibexadataflow';

    protected string $name = 'CodeRhapsodieIbexaDataflowBundle';

    #[\Override]
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new CodeRhapsodieIbexaDataflowExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new FieldComparatorCompilerPass());

        /** @var IbexaCoreExtension $ibexaExtension */
        $ibexaExtension = $container->getExtension('ibexa');
        $ibexaExtension->addPolicyProvider(new PolicyProvider());
    }
}
