<?php

declare(strict_types=1);

namespace NSLSolver\Results;

final readonly class KasadaResult
{
    public function __construct(
        /** @var array<string, string> */
        public array $headers,
        /** USD deducted from the account balance for this solve. */
        public float $cost = 0.0,
        public string $type = 'kasada',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            headers: $data['headers'] ?? [],
            cost: (float) ($data['cost'] ?? 0.0),
            type: $data['type'] ?? 'kasada',
        );
    }
}
