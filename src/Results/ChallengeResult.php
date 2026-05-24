<?php

declare(strict_types=1);

namespace NSLSolver\Results;

final readonly class ChallengeResult
{
    public function __construct(
        /** @var array<string, string> */
        public array $cookies,
        public string $userAgent,
        /** Set when the challenge page returned a Turnstile-style token instead of cookies. */
        public ?string $token = null,
        /** USD deducted from the account balance for this solve. */
        public float $cost = 0.0,
        public string $type = 'challenge',
    ) {}

    public static function fromArray(array $data): self
    {
        $token = $data['token'] ?? null;
        return new self(
            cookies: $data['cookies'] ?? [],
            userAgent: $data['user_agent'] ?? '',
            token: is_string($token) && $token !== '' ? $token : null,
            cost: (float) ($data['cost'] ?? 0.0),
            type: $data['type'] ?? 'challenge',
        );
    }

    /** Shortcut for the cf_clearance cookie. */
    public function cfClearance(): ?string
    {
        return $this->cookies['cf_clearance'] ?? null;
    }
}
