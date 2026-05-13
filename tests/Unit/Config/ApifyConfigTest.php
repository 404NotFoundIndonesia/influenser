<?php

test('config reads apify token from APIFY_TOKEN env', function () {
    config(['influenser.apify.token' => 'test-token-123']);

    expect(config('influenser.apify.token'))->toBe('test-token-123');
});

test('all four apify actor keys exist in config', function () {
    $actors = config('influenser.apify.actors');

    expect($actors)->toBeArray()
        ->toHaveKey('tiktok')
        ->toHaveKey('instagram')
        ->toHaveKey('youtube')
        ->toHaveKey('facebook');
});
