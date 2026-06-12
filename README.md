# NSLSolver PHP SDK

PHP client for the [NSLSolver](https://nslsolver.com) captcha solving API. Handles Cloudflare Turnstile, Cloudflare Challenge, Kasada, Akamai Bot Manager, and reCAPTCHA v3 (incl. Enterprise).

Requires PHP 8.1+. Current version: **1.1.0**.

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
echo $result->token, ' cost=$', $result->cost;

// Challenge (proxy required)
$result = $solver->solveChallenge([
    'url'   => 'https://example.com/protected',
    'proxy' => 'http://user:pass@host:port',
]);
echo $result->cookies['cf_clearance'];
echo $result->userAgent;

// Kasada
$result = $solver->solveKasada([
    'url'        => 'https://example.com/api',
    'user_agent' => 'Mozilla/5.0 ... Chrome/131.0.0.0 ...',
    'ua_version' => 131,
    'kasada_config' => [
        // fp_host / tl_host are bare hostnames (no scheme, no path).
        'p_js_path' => '/149e9513-01fa-4fb0-aad4-566afd725d1b/2d206a39-8ed7-437e-a3be-862e0f06eea3/p.js',
        'fp_host'   => 'passport.twitch.tv',
        'tl_host'   => 'gql.twitch.tv',
    ],
]);
echo $result->headers['x-kpsdk-ct'];
echo $result->headers['x-kpsdk-cd'];

// Akamai (url, user_agent, and proxy all required)
$result = $solver->solveAkamai([
    'url'        => 'https://example.com',
    'user_agent' => 'Mozilla/5.0 ... Chrome/131.0.0.0 ...',
    'proxy'      => 'http://user:pass@host:port',
]);
echo $result->abck();              // the _abck cookie
echo $result->cookies['_abck'];    // same value, raw cookie jar

// reCAPTCHA v3 (site_key, url, and proxy all required)
$result = $solver->solveRecaptchaV3([
    'site_key'   => '6Lc_aCMTAAAAA...',
    'url'        => 'https://example.com',
    'proxy'      => 'http://user:pass@host:port',
    'action'     => 'login',  // optional, server defaults to "verify"
    'enterprise' => true,     // optional, only for reCAPTCHA Enterprise
]);
echo $result->token, ' cost=$', $result->cost;

// Balance
$balance = $solver->getBalance();
echo $balance->balance;       // 42.50
echo $balance->maxCpm;        // 600
echo $balance->currentCpm;    // 12  (tokens consumed in the rolling minute)
echo $balance->cpmLimit;      // 600 (mirror of maxCpm)
echo $balance->unlimited ? 'unlimited' : 'metered';
```

### Options

```php
$solver = new NSLSolver('your-api-key', [
    'base_url'    => 'https://api.nslsolver.com', // default
    'timeout'     => 120,                          // seconds, default
    'max_retries' => 3,                            // retries on 429/503, default
]);
```

Both `solveTurnstile` and `solveChallenge` accept optional `user_agent` and `proxy` params. Turnstile also supports `action` and `cdata`. `solveKasada` accepts an optional `proxy` and an optional `cd_constant` inside `kasada_config`. `solveRecaptchaV3` requires `site_key`, `url`, and `proxy`, and accepts optional `action` (server defaults to `"verify"`), `enterprise` (set `true` for reCAPTCHA Enterprise), and `user_agent`.

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

## Documentation

For more information, check out the full documentation at https://docs.nslsolver.com

## License

MIT
