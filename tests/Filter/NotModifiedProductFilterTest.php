<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Tests\Filter;

use CodeRhapsodie\IbexaDataflowBundle\Filter\NotModifiedContentFilter;
use CodeRhapsodie\IbexaDataflowBundle\Filter\NotModifiedProductFilter;
use CodeRhapsodie\IbexaDataflowBundle\Filter\ProductAttributesComparatorInterface;
use CodeRhapsodie\IbexaDataflowBundle\Model\ProductCreateStructure;
use CodeRhapsodie\IbexaDataflowBundle\Model\ProductUpdateStructure;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NotModifiedProductFilterTest extends TestCase
{
    public function testNotAProductUpdateStructure(): void
    {
        $filter = new NotModifiedProductFilter(
            $this->createStub(NotModifiedContentFilter::class),
            $this->createStub(Connection::class),
            $this->createStub(ProductAttributesComparatorInterface::class),
        );
        $data = new ProductCreateStructure('123', [], 'fre-FR');

        $this->assertSame($data, $filter($data));
    }

    #[DataProvider('provideCases')]
    public function testUpdateContent(bool $contentModified, bool $attributesModified, bool $expectedUpdateContent): void
    {
        $data = new ProductUpdateStructure('123', ['name' => 'foo'], 'fre-FR', 0, ['medicament' => true]);

        $contentFilter = $this->createStub(NotModifiedContentFilter::class);
        $contentFilter->method('__invoke')->willReturn($contentModified ? $data : false);

        $attributesComparator = $this->createStub(ProductAttributesComparatorInterface::class);
        $attributesComparator->method('hasModifiedAttributes')->willReturn($attributesModified);

        $filter = new NotModifiedProductFilter($contentFilter, $this->connection(), $attributesComparator);
        $filter($data);

        $this->assertSame($expectedUpdateContent, $data->isUpdateContent());
    }

    public static function provideCases(): iterable
    {
        yield 'nothing changed' => [false, false, false];
        yield 'content changed' => [true, false, true];
        yield 'only attributes changed' => [false, true, true];
        yield 'both changed' => [true, true, true];
    }

    public function testUnknownProductSkipsComparison(): void
    {
        $data = new ProductUpdateStructure('123', [], 'fre-FR', 0, ['medicament' => true]);

        $attributesComparator = $this->createMock(ProductAttributesComparatorInterface::class);
        $attributesComparator->expects($this->never())->method('hasModifiedAttributes');

        $connection = $this->createStub(Connection::class);
        $result = $this->createStub(Result::class);
        $result->method('fetchFirstColumn')->willReturn([]);
        $connection->method('executeQuery')->willReturn($result);

        $filter = new NotModifiedProductFilter($this->createStub(NotModifiedContentFilter::class), $connection, $attributesComparator);

        $this->assertSame($data, $filter($data));
        $this->assertTrue($data->isUpdateContent());
    }

    private function connection(): Connection
    {
        $contentIdResult = $this->createStub(Result::class);
        $contentIdResult->method('fetchFirstColumn')->willReturn([42]);
        $stockResult = $this->createStub(Result::class);
        $stockResult->method('fetchFirstColumn')->willReturn([]);

        $connection = $this->createStub(Connection::class);
        $connection->method('executeQuery')->willReturnOnConsecutiveCalls($contentIdResult, $stockResult);

        return $connection;
    }
}
