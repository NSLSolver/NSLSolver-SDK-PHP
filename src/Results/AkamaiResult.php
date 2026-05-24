<?php

declare(strict_types=1);

namespace NSLSolver\Results;

/**
 * Result of an Akamai Bot Manager solve. Contains the cookie jar — most
 * importantly `_abck` — to replay on the protected origin paired with the
 * same user agent and proxy/exit IP submitted with the solve.
 */
final readonly class AkamaiResult
{
    public function __construct(
        /** @var array<string, string> */
        public array $cookies,
        /** USD deducted from the account balance for this solve. */
        public float $cost = 0.0,
        public string $type = 'akamai',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cookies: $data['cookies'] ?? [],
            cost: (float) ($data['cost'] ?? 0.0),
            type: $data['type'] ?? 'akamai',
        );
    }

    /** The `_abck` cookie, if present. */
    public function abck(): ?string
    {
        return $this->cookies['_abck'] ?? null;
    }
}
