<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Tests\Filter;

use CodeRhapsodie\IbexaDataflowBundle\Filter\ApiProductAttributesComparator;
use CodeRhapsodie\IbexaDataflowBundle\Filter\DateTimeAttributeValueComparator;
use CodeRhapsodie\IbexaDataflowBundle\Filter\MeasurementAttributeValueComparator;
use CodeRhapsodie\IbexaDataflowBundle\Model\ProductUpdateStructure;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Contracts\ProductCatalog\ProductServiceInterface;
use Ibexa\Contracts\ProductCatalog\Values\AttributeDefinitionInterface;
use Ibexa\Contracts\ProductCatalog\Values\AttributeInterface;
use Ibexa\Contracts\ProductCatalog\Values\AttributeTypeInterface;
use Ibexa\Contracts\ProductCatalog\Values\ProductInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ApiProductAttributesComparatorTest extends TestCase
{
    public function testNoAttributesNeverLoadsTheProduct(): void
    {
        $productService = $this->createMock(ProductServiceInterface::class);
        $productService->expects($this->never())->method('getProduct');

        $this->assertFalse(($this->comparator($productService))->hasModifiedAttributes($this->struct([])));
    }

    public function testUnknownProductIsModified(): void
    {
        $productService = $this->createStub(ProductServiceInterface::class);
        $productService->method('getProduct')->willThrowException(new NotFoundException('product', 'code'));

        $this->assertTrue(($this->comparator($productService))->hasModifiedAttributes($this->struct(['medicament' => true])));
    }

    /**
     * @param array<string, mixed> $stored
     * @param array<string, mixed> $expected
     */
    #[DataProvider('provideAttributes')]
    public function testHasModifiedAttributes(array $stored, array $expected, bool $modified): void
    {
        $productService = $this->createMock(ProductServiceInterface::class);
        $productService->expects($this->once())->method('getProduct')->with('123')->willReturn($this->product($stored));

        $this->assertSame($modified, ($this->comparator($productService))->hasModifiedAttributes($this->struct($expected)));
    }

    public static function provideAttributes(): iterable
    {
        yield 'identical boolean' => [['medicament' => true], ['medicament' => true], false];
        yield 'identical false boolean' => [['medicament' => false], ['medicament' => false], false];
        yield 'different boolean' => [['medicament' => false], ['medicament' => true], true];
        yield 'unset value (null)' => [['medicament' => null], ['medicament' => false], true];
        yield 'missing attribute' => [[], ['medicament' => false], true];
        yield 'other attribute only' => [['bio' => true], ['medicament' => true], true];
        yield 'extra stored attributes are ignored' => [['medicament' => true, 'bio' => false], ['medicament' => true], false];
        yield 'one of several differs' => [['medicament' => true, 'bio' => false], ['medicament' => true, 'bio' => true], true];
        // integer
        yield 'identical integer' => [['size' => 3], ['size' => 3], false];
        yield 'different integer' => [['size' => 3], ['size' => 4], true];
        yield 'integer imported as string' => [['size' => 3], ['size' => '3'], false];
        // float
        yield 'identical float' => [['weight' => 1.5], ['weight' => 1.5], false];
        yield 'different float' => [['weight' => 1.5], ['weight' => 1.6], true];
        yield 'float stored as whole number, imported as int' => [['weight' => 3.0], ['weight' => 3], false];
        yield 'float imported as string' => [['weight' => 1.5], ['weight' => '1.5'], false];
        yield 'zero float vs zero' => [['weight' => 0.0], ['weight' => 0], false];
        // color, selection and symbol are strings
        yield 'identical color' => [['color' => '#ff0000'], ['color' => '#ff0000'], false];
        yield 'different color' => [['color' => '#ff0000'], ['color' => '#00ff00'], true];
        yield 'identical selection' => [['size_option' => 'xl'], ['size_option' => 'xl'], false];
        yield 'different selection' => [['size_option' => 'xl'], ['size_option' => 'm'], true];
        yield 'numeric looking selection is compared as value' => [['code' => '007'], ['code' => '7'], false];
        yield 'empty string is a value' => [['label' => ''], ['label' => ''], false];
        // boolean edge cases
        yield 'boolean stored as int' => [['medicament' => 1], ['medicament' => true], false];
        yield 'boolean imported as int' => [['medicament' => false], ['medicament' => 0], false];
        yield 'boolean mismatch with int' => [['medicament' => true], ['medicament' => 0], true];
        // value objects (measurement, datetime...)
        yield 'object stored, scalar expected' => [['length' => new \stdClass()], ['length' => 5], true];
        yield 'object stored, array expected: unknown type is modified' => [['length' => new \stdClass()], ['length' => ['value' => 5, 'unit' => 'cm']], true];
        yield 'different non scalar' => [['colors' => ['red']], ['colors' => ['blue']], true];
        yield 'non scalar unset' => [['colors' => null], ['colors' => ['blue']], true];
    }

    public function testComparatorIsChosenByAttributeType(): void
    {
        $productService = $this->createStub(ProductServiceInterface::class);
        $productService->method('getProduct')->willReturn($this->product(
            ['released' => new \DateTimeImmutable('2025-01-02 03:04:05 UTC'), 'weight' => 1.5],
            ['released' => 'datetime']
        ));
        $comparator = $this->comparator($productService);

        $this->assertFalse($comparator->hasModifiedAttributes($this->struct(['released' => '2025-01-02T03:04:05+00:00', 'weight' => 1.5])));
        $this->assertTrue($comparator->hasModifiedAttributes($this->struct(['released' => '2025-01-03T03:04:05+00:00'])));
    }

    private function comparator(ProductServiceInterface $productService): ApiProductAttributesComparator
    {
        return new ApiProductAttributesComparator($productService, [
            new DateTimeAttributeValueComparator(),
            new MeasurementAttributeValueComparator(),
        ]);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function struct(array $attributes): ProductUpdateStructure
    {
        return new ProductUpdateStructure('123', [], 'fre-FR', 0, $attributes);
    }

    /**
     * @param array<string, mixed>  $attributes
     * @param array<string, string> $types      attribute type per identifier, "checkbox" by default
     */
    private function product(array $attributes, array $types = []): ProductInterface
    {
        $list = [];
        foreach ($attributes as $identifier => $value) {
            $attribute = $this->createStub(AttributeInterface::class);
            $attribute->method('getIdentifier')->willReturn((string) $identifier);
            $attribute->method('getValue')->willReturn($value);
            $type = $this->createStub(AttributeTypeInterface::class);
            $type->method('getIdentifier')->willReturn($types[$identifier] ?? 'checkbox');
            $definition = $this->createStub(AttributeDefinitionInterface::class);
            $definition->method('getType')->willReturn($type);
            $attribute->method('getAttributeDefinition')->willReturn($definition);
            $list[] = $attribute;
        }

        $product = $this->createStub(ProductInterface::class);
        $product->method('getAttributes')->willReturn($list);

        return $product;
    }
}
