<?php

declare(strict_types=1);

namespace NSLSolver\Results;

final readonly class TurnstileResult
{
    public function __construct(
        public string $token,
        /** USD deducted from the account balance for this solve. */
        public float $cost = 0.0,
        public string $type = 'turnstile',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            token: $data['token'] ?? '',
            cost: (float) ($data['cost'] ?? 0.0),
            type: $data['type'] ?? 'turnstile',
        );
    }
}
