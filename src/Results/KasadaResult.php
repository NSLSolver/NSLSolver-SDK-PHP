<?php

declare(strict_types=1);

namespace NSLSolver\Results;

final readonly class KasadaResult
{
    public function __construct(
        public array $headers,
        public string $type = 'kasada',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            headers: $data['headers'] ?? [],
            type: $data['type'] ?? 'kasada',
        );
    }
}
