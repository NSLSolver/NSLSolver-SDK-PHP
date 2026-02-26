# NSLSolver PHP SDK

PHP client for the [NSLSolver](https://nslsolver.com) captcha solving API. Handles Cloudflare Turnstile and Challenge captchas.

Requires PHP 8.1+.

## Install

```bash
composer require nslsolver/nslsolver
```

## Usage

```php
use NSLSolver\NSLSolver;

$solver = new NSLSolver('your-api-key');

// Turnstile
$result = $solver->solveTurnstile([
    'site_key' => '0x4AAAAAAAB...',
    'url'      => 'https://example.com',
]);
echo $result->token;

// Challenge (proxy required)
$result = $solver->solveChallenge([
    'url'   => 'https://example.com/protected',
    'proxy' => 'http://user:pass@host:port',
]);
echo $result->cookies['cf_clearance'];
echo $result->userAgent;

// Balance
$balance = $solver->getBalance();
echo $balance->balance;       // 42.50
echo $balance->maxThreads;    // 10
```

### Options

```php
$solver = new NSLSolver('your-api-key', [
    'base_url'    => 'https://api.nslsolver.com', // default
    'timeout'     => 120,                          // seconds, default
    'max_retries' => 3,                            // retries on 429/503, default
]);
```

Both `solveTurnstile` and `solveChallenge` accept optional `user_agent` and `proxy` params. Turnstile also supports `action` and `cdata`.

## Error Handling

All exceptions extend `NSLSolverException`. Catch specific ones or the base class:

```php
use NSLSolver\Exceptions\AuthenticationException;
use NSLSolver\Exceptions\InsufficientBalanceException;
use NSLSolver\Exceptions\RateLimitException;
use NSLSolver\Exceptions\SolveException;
use NSLSolver\Exceptions\NSLSolverException;

try {
    $result = $solver->solveTurnstile([...]);
} catch (AuthenticationException $e) {
    // bad api key (401)
} catch (InsufficientBalanceException $e) {
    // need to top up (402)
} catch (RateLimitException $e) {
    // 429, thrown after retries exhausted
} catch (SolveException $e) {
    // 400 or 503
} catch (NSLSolverException $e) {
    // catch-all -- $e->response has the raw API body
}
```

Requests that get 429 or 503 are retried automatically with exponential backoff (up to `max_retries`). Errors 400/401/402/403 fail immediately.

## License

MIT
