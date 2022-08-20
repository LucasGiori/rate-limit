<?php

namespace RateLimit;

use DateTime;
use DateTimeImmutable;

class Status
{
    private function __construct(
        private string $identifier,
        private bool $success,
        private int $limit,
        private int $remainingAttempts,
        private DateTimeImmutable $resetAt,
        private int $resetAtInSeconds
    ) {
    }

    public static function from(string $identifier, int $current, int $limit, int $resetTime): static
    {
        $resetAt = new DateTimeImmutable("@$resetTime");
        $resetInSeconds = $resetAt->getTimestamp() - (new DateTime())->getTimestamp();

        return new static(
            identifier: $identifier,
            success: $current <= $limit,
            limit: $limit,
            remainingAttempts: max(0, $limit - $current),
            resetAt: $resetAt,
            resetAtInSeconds: $resetInSeconds
        );
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getRemainingAttempts(): int
    {
        return $this->remainingAttempts;
    }

    public function getResetAt(): DateTimeImmutable
    {
        return $this->resetAt;
    }

    public function getResetAtInSeconds(): int
    {
        return $this->resetAtInSeconds;
    }
}
