<?php

declare(strict_types=1);

namespace NSLSolver\Results;

/**
 * Result of a reCAPTCHA v3 (incl. Enterprise) solve. Contains the response
 * token to replay on the protected origin.
 *
 * Note: the API serves reCAPTCHA v3 through the same response path as
 * Turnstile, so the body carries `token` (and may carry `cost`). The `type`
 * field is the hyphenated slug `recaptcha-v3` rather than the bare
 * `recaptchav3` request discriminator.
 */
final readonly class RecaptchaResult
{
    public function __construct(
        public string $token,
        /** USD deducted from the account balance for this solve. */
        public float $cost = 0.0,
        public string $type = 'recaptcha-v3',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            token: $data['token'] ?? '',
            cost: (float) ($data['cost'] ?? 0.0),
            type: $data['type'] ?? 'recaptcha-v3',
        );
    }
}
