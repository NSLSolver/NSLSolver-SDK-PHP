<?php

declare(strict_types=1);

namespace NSLSolver\Exceptions;

/** Rate limit exceeded (429). Thrown after all retries are exhausted. */
class RateLimitException extends NSLSolverException {}
