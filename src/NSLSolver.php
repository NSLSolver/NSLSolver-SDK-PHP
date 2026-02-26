<?php

declare(strict_types=1);

namespace NSLSolver;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use NSLSolver\Exceptions\AuthenticationException;
use NSLSolver\Exceptions\InsufficientBalanceException;
use NSLSolver\Exceptions\NSLSolverException;
use NSLSolver\Exceptions\RateLimitException;
use NSLSolver\Exceptions\SolveException;
use NSLSolver\Exceptions\TypeNotAllowedException;
use NSLSolver\Results\BalanceResult;
use NSLSolver\Results\ChallengeResult;
use NSLSolver\Results\TurnstileResult;

/** API client for solving captchas via NSLSolver. */
class NSLSolver
{
    private const DEFAULT_BASE_URL = 'https://api.nslsolver.com';
    private const DEFAULT_TIMEOUT = 120;
    private const DEFAULT_MAX_RETRIES = 3;
    private const RETRYABLE_STATUS_CODES = [429, 503];
    private const BASE_BACKOFF_MS = 1000;

    private readonly Client $httpClient;
    private readonly string $baseUrl;
    private readonly int $timeout;
    private readonly int $maxRetries;

    public function __construct(
        private readonly string $apiKey,
        array $options = [],
    ) {
        $this->baseUrl = rtrim($options['base_url'] ?? self::DEFAULT_BASE_URL, '/');
        $this->timeout = $options['timeout'] ?? self::DEFAULT_TIMEOUT;
        $this->maxRetries = $options['max_retries'] ?? self::DEFAULT_MAX_RETRIES;

        $this->httpClient = $options['http_client'] ?? new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'headers' => [
                'X-API-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /** Solve a Cloudflare Turnstile captcha. Requires site_key and url. */
    public function solveTurnstile(array $params): TurnstileResult
    {
        $this->validateRequired($params, ['site_key', 'url']);

        $payload = array_filter([
            'type' => 'turnstile',
            'site_key' => $params['site_key'],
            'url' => $params['url'],
            'action' => $params['action'] ?? null,
            'cdata' => $params['cdata'] ?? null,
            'proxy' => $params['proxy'] ?? null,
            'user_agent' => $params['user_agent'] ?? null,
        ], static fn (mixed $v): bool => $v !== null);

        return TurnstileResult::fromArray(
            $this->requestWithRetry('POST', '/solve', $payload)
        );
    }

    /** Solve a Cloudflare Challenge page. Requires url and proxy. */
    public function solveChallenge(array $params): ChallengeResult
    {
        $this->validateRequired($params, ['url', 'proxy']);

        $payload = array_filter([
            'type' => 'challenge',
            'url' => $params['url'],
            'proxy' => $params['proxy'],
            'user_agent' => $params['user_agent'] ?? null,
        ], static fn (mixed $v): bool => $v !== null);

        return ChallengeResult::fromArray(
            $this->requestWithRetry('POST', '/solve', $payload)
        );
    }

    /** Get current account balance and limits. */
    public function getBalance(): BalanceResult
    {
        return BalanceResult::fromArray(
            $this->requestWithRetry('GET', '/balance')
        );
    }

    /**
     * HTTP request with retry + exponential backoff for 429/503.
     *
     * @return array Decoded JSON response
     */
    private function requestWithRetry(string $method, string $path, ?array $payload = null): array
    {
        $lastException = null;

        for ($attempt = 0; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $options = $payload !== null ? ['json' => $payload] : [];
                $response = $this->httpClient->request($method, $path, $options);
                $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

                if (!is_array($data)) {
                    throw new NSLSolverException('Unexpected response format from API', 0, []);
                }

                return $data;
            } catch (ClientException | ServerException $e) {
                $statusCode = $e->getResponse()->getStatusCode();
                $body = $e->getResponse()->getBody()->getContents();

                try {
                    $responseData = json_decode($body, true, 512, JSON_THROW_ON_ERROR) ?? [];
                } catch (\JsonException) {
                    $responseData = [];
                }

                $msg = $responseData['error'] ?? $responseData['message'] ?? $e->getMessage();

                $lastException = match ($statusCode) {
                    400 => new SolveException($msg, $statusCode, $responseData, $e),
                    401 => new AuthenticationException($msg, $statusCode, $responseData, $e),
                    402 => new InsufficientBalanceException($msg, $statusCode, $responseData, $e),
                    403 => new TypeNotAllowedException($msg, $statusCode, $responseData, $e),
                    429 => new RateLimitException($msg, $statusCode, $responseData, $e),
                    503 => new SolveException($msg, $statusCode, $responseData, $e),
                    default => new NSLSolverException($msg, $statusCode, $responseData, $e),
                };

                if (!in_array($statusCode, self::RETRYABLE_STATUS_CODES, true)) {
                    throw $lastException;
                }

                if ($attempt < $this->maxRetries) {
                    $this->backoff($attempt);
                }
            } catch (GuzzleException $e) {
                $lastException = new NSLSolverException(
                    'HTTP request failed: ' . $e->getMessage(), 0, [], $e,
                );

                if ($attempt < $this->maxRetries) {
                    $this->backoff($attempt);
                }
            } catch (\JsonException $e) {
                throw new NSLSolverException(
                    'Failed to decode API response: ' . $e->getMessage(), 0, [], $e,
                );
            }
        }

        throw $lastException ?? new NSLSolverException('Request failed after all retries');
    }

    private function validateRequired(array $params, array $required): void
    {
        foreach ($required as $key) {
            if (!isset($params[$key]) || $params[$key] === '') {
                throw new NSLSolverException("Missing required parameter: '{$key}'", 400);
            }
        }
    }

    /** Exponential backoff with +/-25% jitter. */
    private function backoff(int $attempt): void
    {
        $delayMs = self::BASE_BACKOFF_MS * (2 ** $attempt);
        $jitter = (int) ($delayMs * 0.25);
        $delayMs += random_int(-$jitter, $jitter);
        usleep($delayMs * 1000);
    }
}
