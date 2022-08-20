<?php

namespace RateLimit;

use RateLimit\Exception\InvalidValue;

class Rate
{
    /**
     * @throws InvalidValue
     */
    private function __construct(
        private int $operations,
        private int $interval
    ) {
        $this->validate();
    }

    /**
     * @throws InvalidValue
     */
    public static function perSecond(int $operations): Rate
    {
        return new static(operations: $operations, interval: IntervalInSeconds::SECOND->value);
    }

    /**
     * @throws InvalidValue
     */
    public static function perMinute(int $operations): Rate
    {
        return new static(operations: $operations, interval: IntervalInSeconds::MINUTE->value);
    }

    /**
     * @throws InvalidValue
     */
    public static function perHour(int $operations): Rate
    {
        return new static(operations: $operations, interval: IntervalInSeconds::HOUR->value);
    }

    /**
     * @throws InvalidValue
     */
    public static function perDay(int $operations): Rate
    {
        return new static(operations: $operations, interval: IntervalInSeconds::DAY->value);
    }

    /**
     * @throws InvalidValue
     */
    public static function custom(int $operations, int $seconds): Rate
    {
        return new static(operations: $operations, interval: $seconds);
    }

    /**
     * @throws InvalidValue
     */
    private function validate()
    {
        if ($this->operations <= 0) {
            throw new InvalidValue(message: "Number of operations must be greater than zero");
        }

        if ($this->interval <= 0) {
            throw new InvalidValue(message: "Seconds interval must be greater than zero");
        }
    }

    public function getOperations(): int
    {
        return $this->operations;
    }

    public function getInterval(): int
    {
        return $this->interval;
    }
}
