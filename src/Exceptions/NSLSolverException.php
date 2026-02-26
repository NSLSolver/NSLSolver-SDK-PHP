<?php

declare(strict_types=1);

namespace NSLSolver\Exceptions;

use RuntimeException;

/** Base exception for all NSLSolver errors. */
class NSLSolverException extends RuntimeException
{
    public function __construct(
        string $message,
        int $code = 0,
        public readonly array $response = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
