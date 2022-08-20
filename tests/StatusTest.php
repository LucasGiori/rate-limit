<?php

namespace RateLimit\Tests;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RateLimit\Status;

class StatusTest extends TestCase
{
    public function testShouldReturnAStatusInstance()
    {
        $status = Status::from(
            identifier: 'test_instance',
            current: 2,
            limit: 3,
            resetTime: 1660611866
        );

        static::assertEquals(expected: 'test_instance', actual: $status->getIdentifier());
        static::assertEquals(expected: 1, actual: $status->getRemainingAttempts());
        static::assertEquals(expected: true, actual: $status->isSuccess());
        static::assertEquals(expected: 3, actual: $status->getLimit());
        static::assertIsInt(actual: $status->getResetAtInSeconds());
        static::assertInstanceOf(expected: DateTimeImmutable::class, actual: $status->getResetAt());
    }
}
