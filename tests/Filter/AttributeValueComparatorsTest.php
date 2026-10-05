<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Tests\Filter;

use CodeRhapsodie\IbexaDataflowBundle\Filter\DateTimeAttributeValueComparator;
use CodeRhapsodie\IbexaDataflowBundle\Filter\MeasurementAttributeValueComparator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AttributeValueComparatorsTest extends TestCase
{
    public function testSupportedTypes(): void
    {
        $this->assertTrue(new DateTimeAttributeValueComparator()->supports('datetime'));
        $this->assertFalse(new DateTimeAttributeValueComparator()->supports('measurement'));
        $this->assertTrue(new MeasurementAttributeValueComparator()->supports('measurement'));
        $this->assertFalse(new MeasurementAttributeValueComparator()->supports('datetime'));
    }

    #[DataProvider('provideDates')]
    public function testDateTime(mixed $stored, mixed $expected, bool $same): void
    {
        $this->assertSame($same, new DateTimeAttributeValueComparator()->isSame($stored, $expected));
    }

    public static function provideDates(): iterable
    {
        $date = new \DateTimeImmutable('2025-01-02 03:04:05 UTC');

        yield 'same object' => [$date, new \DateTime('2025-01-02 03:04:05 UTC'), true];
        yield 'object vs string' => [$date, '2025-01-02T03:04:05+00:00', true];
        yield 'same instant, other timezone' => [$date, '2025-01-02T04:04:05+01:00', true];
        yield 'string vs string' => ['2025-01-02 03:04:05 UTC', '2025-01-02T03:04:05Z', true];
        yield 'different' => [$date, '2025-01-03T03:04:05+00:00', false];
        yield 'invalid string' => [$date, 'not a date', false];
        yield 'invalid stored' => ['not a date', 'not a date', false];
        yield 'not a date' => [$date, 12, false];
    }

    #[DataProvider('provideMeasurements')]
    public function testMeasurement(mixed $stored, mixed $expected, bool $same): void
    {
        $this->assertSame($same, new MeasurementAttributeValueComparator()->isSame($stored, $expected));
    }

    public static function provideMeasurements(): iterable
    {
        yield 'same simple value' => [self::simple(5, 'cm'), self::simple(5.0, 'cm'), true];
        yield 'other value' => [self::simple(5, 'cm'), self::simple(6, 'cm'), false];
        yield 'other unit' => [self::simple(5, 'cm'), self::simple(5, 'mm'), false];
        yield 'same range' => [self::range(1, 5, 'cm'), self::range(1, 5, 'cm'), true];
        yield 'other range' => [self::range(1, 5, 'cm'), self::range(1, 6, 'cm'), false];
        yield 'range vs simple' => [self::range(1, 5, 'cm'), self::simple(5, 'cm'), false];
        yield 'scalar expected' => [self::simple(5, 'cm'), 5, false];
        yield 'array expected' => [self::simple(5, 'cm'), ['value' => 5, 'unit' => 'cm'], false];
        yield 'unknown objects' => [new \stdClass(), new \stdClass(), false];
    }

    private static function simple(int|float $value, string $unit): object
    {
        return new readonly class($value, $unit) {
            public function __construct(private int|float $value, private string $unit)
            {
            }

            public function getValue(): int|float
            {
                return $this->value;
            }

            public function getUnit(): string
            {
                return $this->unit;
            }
        };
    }

    private static function range(int|float $min, int|float $max, string $unit): object
    {
        return new readonly class($min, $max, $unit) {
            public function __construct(private int|float $min, private int|float $max, private string $unit)
            {
            }

            public function getMinValue(): int|float
            {
                return $this->min;
            }

            public function getMaxValue(): int|float
            {
                return $this->max;
            }

            public function getUnit(): string
            {
                return $this->unit;
            }
        };
    }
}
