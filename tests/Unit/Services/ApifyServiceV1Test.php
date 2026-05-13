<?php

use App\Services\ThirdParty\ApifyServiceV1;
use Illuminate\Support\Facades\Http;

test('runActor returns parsed array on 200 response and uses bearer token', function () {
    Http::fake([
        '*' => Http::response([['followers' => 100_000, 'engagement_rate' => 4.5]], 200),
    ]);

    $service = new ApifyServiceV1('test-token');
    $result = $service->runActor('actor~tiktok-scraper', ['profiles' => ['testuser']]);

    expect($result)->toBeArray()
        ->and($result[0]['followers'])->toBe(100_000);

    Http::assertSent(function ($request) {
        return str_contains($request->header('Authorization')[0] ?? '', 'Bearer test-token');
    });
});

test('runActor throws exception on non-2xx response', function () {
    Http::fake([
        '*' => Http::response('Internal Server Error', 500),
    ]);

    $service = new ApifyServiceV1('test-token');

    expect(fn () => $service->runActor('actor~tiktok-scraper', []))
        ->toThrow(\RuntimeException::class);
});
