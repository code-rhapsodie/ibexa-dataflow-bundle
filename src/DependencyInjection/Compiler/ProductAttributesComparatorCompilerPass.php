<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\DependencyInjection\Compiler;

use CodeRhapsodie\IbexaDataflowBundle\Filter\ApiProductAttributesComparator;
use CodeRhapsodie\IbexaDataflowBundle\Filter\NullProductAttributesComparator;
use CodeRhapsodie\IbexaDataflowBundle\Filter\ProductAttributesComparatorInterface;
use Ibexa\Contracts\ProductCatalog\ProductServiceInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * The default comparator needs ibexa/product-catalog. Without it, falls back to a comparator that ignores attributes.
 */
class ProductAttributesComparatorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->has(ProductServiceInterface::class)) {
            return;
        }

        $container->removeDefinition(ApiProductAttributesComparator::class);
        $container->setDefinition(NullProductAttributesComparator::class, new Definition(NullProductAttributesComparator::class));

        if (!$container->hasAlias(ProductAttributesComparatorInterface::class)
            || (string) $container->getAlias(ProductAttributesComparatorInterface::class) === ApiProductAttributesComparator::class) {
            $container->setAlias(ProductAttributesComparatorInterface::class, new Alias(NullProductAttributesComparator::class));
        }
    }
}
