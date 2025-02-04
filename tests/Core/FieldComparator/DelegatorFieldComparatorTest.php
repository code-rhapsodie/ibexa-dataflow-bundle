<?php

namespace CodeRhapsodie\IbexaDataflowBundle\Tests\Core\FieldComparator;

use CodeRhapsodie\IbexaDataflowBundle\Core\FieldComparator\DelegatorFieldComparator;
use CodeRhapsodie\IbexaDataflowBundle\Core\FieldComparator\FieldComparatorInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use PHPUnit\Framework\TestCase;

class DelegatorFieldComparatorTest extends TestCase
{
    private DelegatorFieldComparator $delegatorFieldComparator;

    protected function setUp(): void
    {
        $type1FieldComparatorMock = $this->createMock(FieldComparatorInterface::class);
        $type1FieldComparatorMock->method('compare')->willReturnCallback(fn(Field $field, $hash) => $hash === 'rightValue1');
        $type2FieldComparatorMock = $this->createMock(FieldComparatorInterface::class);
        $type2FieldComparatorMock->method('compare')->willReturnCallback(fn(Field $field, $hash) => $hash === 'rightValue2');
        $this->delegatorFieldComparator = new DelegatorFieldComparator();
        $this->delegatorFieldComparator->registerDelegateFieldComparator($type1FieldComparatorMock, 'type1');
        $this->delegatorFieldComparator->registerDelegateFieldComparator($type2FieldComparatorMock, 'type2');
    }

    /**
     * @dataProvider fieldProvider
     */
    public function testField(string $type, bool $expected, $hash)
    {
        $field = new Field(['fieldTypeIdentifier' => $type]);
        $return = $this->delegatorFieldComparator->compare($field, $hash);

        $this->assertSame($expected, $return);
    }

    public function fieldProvider(): iterable
    {
        yield ['type1', true, 'rightValue1'];
        yield ['type1', false, 'wrongValue'];
        yield ['type2', true, 'rightValue2'];
        yield ['type2', false, 'wrongValue'];
        yield ['otherType', false, 'rightValue1'];
    }
}
