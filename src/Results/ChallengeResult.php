<?php

declare(strict_types=1);

namespace NSLSolver\Results;

final readonly class ChallengeResult
{
    public function __construct(
        public array $cookies,
        public string $userAgent,
        public string $type = 'challenge',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cookies: $data['cookies'] ?? [],
            userAgent: $data['user_agent'] ?? '',
            type: $data['type'] ?? 'challenge',
        );
    }
}
