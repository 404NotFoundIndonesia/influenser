<?php

namespace App\Services\ThirdParty;

use Illuminate\Support\Facades\Http;

class ApifyServiceV1 implements ApifyServiceInterface
{
    private const BASE_URL = 'https://api.apify.com/v2/acts';

    public function __construct(private readonly string $token) {}

    public function runActor(string $actorId, array $input): array
    {
        $url = sprintf('%s/%s/run-sync-get-dataset-items', self::BASE_URL, $actorId);

        $response = Http::withToken($this->token)
            ->post($url, $input);

        if (! $response->successful()) {
            throw new \RuntimeException(
                "Apify actor run failed [{$response->status()}]: {$response->body()}"
            );
        }

        return $response->json() ?? [];
    }
}
