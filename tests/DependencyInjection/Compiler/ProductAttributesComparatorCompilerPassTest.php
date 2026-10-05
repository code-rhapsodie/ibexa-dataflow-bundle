<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Tests\DependencyInjection\Compiler;

use CodeRhapsodie\IbexaDataflowBundle\DependencyInjection\Compiler\ProductAttributesComparatorCompilerPass;
use CodeRhapsodie\IbexaDataflowBundle\Filter\ApiProductAttributesComparator;
use CodeRhapsodie\IbexaDataflowBundle\Filter\NullProductAttributesComparator;
use CodeRhapsodie\IbexaDataflowBundle\Filter\ProductAttributesComparatorInterface;
use Ibexa\Contracts\ProductCatalog\ProductServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ProductAttributesComparatorCompilerPassTest extends TestCase
{
    public function testKeepsDefaultWhenProductCatalogIsAvailable(): void
    {
        $container = $this->container();
        $container->setDefinition(ProductServiceInterface::class, new Definition(\stdClass::class));

        (new ProductAttributesComparatorCompilerPass())->process($container);

        $this->assertTrue($container->hasDefinition(ApiProductAttributesComparator::class));
        $this->assertSame(ApiProductAttributesComparator::class, (string) $container->getAlias(ProductAttributesComparatorInterface::class));
    }

    public function testFallsBackWithoutProductCatalog(): void
    {
        $container = $this->container();

        (new ProductAttributesComparatorCompilerPass())->process($container);

        $this->assertFalse($container->hasDefinition(ApiProductAttributesComparator::class));
        $this->assertSame(NullProductAttributesComparator::class, (string) $container->getAlias(ProductAttributesComparatorInterface::class));
    }

    public function testKeepsCustomComparatorWithoutProductCatalog(): void
    {
        $container = $this->container();
        $container->setAlias(ProductAttributesComparatorInterface::class, 'app.comparator');

        (new ProductAttributesComparatorCompilerPass())->process($container);

        $this->assertSame('app.comparator', (string) $container->getAlias(ProductAttributesComparatorInterface::class));
    }

    private function container(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setDefinition(ApiProductAttributesComparator::class, new Definition(ApiProductAttributesComparator::class));
        $container->setAlias(ProductAttributesComparatorInterface::class, ApiProductAttributesComparator::class);

        return $container;
    }
}
