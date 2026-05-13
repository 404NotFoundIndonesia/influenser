<?php

namespace App\Services\ThirdParty;

interface ApifyServiceInterface
{
    public function runActor(string $actorId, array $input): array;
}
