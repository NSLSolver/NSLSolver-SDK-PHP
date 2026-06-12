<?php

declare(strict_types=1);

namespace NSLSolver\Results;

/**
 * Account balance, plan flags, allowed captcha types, and live CPM (captchas-per-minute) usage.
 */
final readonly class BalanceResult
{
    public function __construct(
        public float $balance = 0.0,
        public bool $unlimited = false,
        /** @var list<string> */
        public array $allowedTypes = [],
        /** Per-key captchas-per-minute ceiling. 0 means uncapped. */
        public int $maxCpm = 0,
        /** Tokens consumed in the rolling CPM window. */
        public int $currentCpm = 0,
        /** Mirror of $maxCpm — useful for dashboards. */
        public int $cpmLimit = 0,
        public ?string $unlimitedExpiresAt = null,
        public array $raw = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $maxCpm = (int) ($data['max_cpm'] ?? 0);
        return new self(
            balance: (float) ($data['balance'] ?? 0.0),
            unlimited: (bool) ($data['unlimited'] ?? false),
            allowedTypes: array_values($data['allowed_types'] ?? []),
            maxCpm: $maxCpm,
            currentCpm: (int) ($data['current_cpm'] ?? 0),
            cpmLimit: (int) ($data['cpm_limit'] ?? $maxCpm),
            unlimitedExpiresAt: isset($data['unlimited_expires_at']) ? (string) $data['unlimited_expires_at'] : null,
            raw: $data,
        );
    }
}
