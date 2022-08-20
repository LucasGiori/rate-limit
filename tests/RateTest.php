<?php

namespace RateLimit\Tests;

use PHPUnit\Framework\TestCase;
use RateLimit\Exception\InvalidValue;
use RateLimit\IntervalInSeconds;
use RateLimit\Rate;

class RateTest extends TestCase
{
    public function testShouldReturnAnInstanceOfTheDefinedRatePerSecond()
    {
        $rate = Rate::perSecond(operations: 150);
        static::assertEquals(expected: 150, actual: $rate->getOperations());
        static::assertEquals(expected: IntervalInSeconds::SECOND->value, actual: $rate->getInterval());
    }

    public function testShouldReturnAnInstanceOfTheDefinedRatePerMinute()
    {
        $rate = Rate::perMinute(operations: 1500);
        static::assertEquals(expected: 1500, actual: $rate->getOperations());
        static::assertEquals(expected: IntervalInSeconds::MINUTE->value, actual: $rate->getInterval());
    }

    public function testShouldReturnAnInstanceOfTheDefinedRatePerHour()
    {
        $rate = Rate::perHour(operations: 10500);
        static::assertEquals(expected: 10500, actual: $rate->getOperations());
        static::assertEquals(expected: IntervalInSeconds::HOUR->value, actual: $rate->getInterval());
    }

    public function testShouldReturnAnInstanceOfTheDefinedRatePerDay()
    {
        $rate = Rate::perDay(operations: 100500);
        static::assertEquals(expected: 100500, actual: $rate->getOperations());
        static::assertEquals(expected: IntervalInSeconds::DAY->value, actual: $rate->getInterval());
    }

    public function testShouldReturnAnInstanceOfTheCustomRate()
    {
        $rate = Rate::custom(operations: 201000, seconds: IntervalInSeconds::DAY->value * 5);
        static::assertEquals(expected: 201000, actual: $rate->getOperations());
        static::assertEquals(expected: 432000, actual: $rate->getInterval());
    }

    public function testShouldReturnAnInvalidValueExceptionForTheAmountOfOperations()
    {
        static::expectException(exception: InvalidValue::class);
        static::expectExceptionMessage(message: 'Number of operations must be greater than zero');

        Rate::perSecond(operations: 0);
    }

    public function testShouldReturnAnInvalidValueExceptionForTheAmountOfOperationsEqualToZero()
    {
        static::expectException(exception: InvalidValue::class);
        static::expectExceptionMessage(message: 'Number of operations must be greater than zero');

        Rate::perSecond(operations: 0);
    }

    public function testShouldReturnAnInvalidValueExceptionForTheAmountOfSecondsEqualToZero()
    {
        static::expectException(exception: InvalidValue::class);
        static::expectExceptionMessage(message: 'Seconds interval must be greater than zero');

        Rate::custom(operations: 10, seconds: 0);
    }
}