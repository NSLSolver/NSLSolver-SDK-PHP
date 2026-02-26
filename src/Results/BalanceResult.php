<?php

declare(strict_types=1);

namespace NSLSolver\Results;

final readonly class BalanceResult
{
    public function __construct(
        public float $balance,
        public int $maxThreads,
        public array $allowedTypes,
        public array $raw = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            balance: (float) ($data['balance'] ?? 0.0),
            maxThreads: (int) ($data['max_threads'] ?? 0),
            allowedTypes: $data['allowed_types'] ?? [],
            raw: $data,
        );
    }
}
