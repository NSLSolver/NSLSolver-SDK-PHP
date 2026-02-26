<?php

declare(strict_types=1);

namespace NSLSolver\Results;

final readonly class TurnstileResult
{
    public function __construct(
        public string $token,
        public string $type = 'turnstile',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            token: $data['token'] ?? '',
            type: $data['type'] ?? 'turnstile',
        );
    }
}
