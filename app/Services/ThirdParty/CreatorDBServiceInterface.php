<?php

namespace App\Services\ThirdParty;

interface CreatorDBServiceInterface
{
    public function checkAPIStatus(): array;
    public function tiktokBasic(string $username): array;
    public function tiktokHistory(string $username): array;
    public function tiktokAdvancedSearch(array $filter): array;
    public function instagramBasic(string $username): array;
    public function instagramHistory(string $username): array;
    public function instagramAdvancedSearch(array $filter): array;
    public function youtubeBasic(string $username): array;
    public function youtubeHistory(string $username): array;
    public function youtubeDetail(string $username): array;
    public function youtubeEmail(string $username): array;
    public function youtubeAdvancedSearch(array $filter): array;
    public function facebookBasic(string $username): array;
    public function facebookHistory(string $username): array;
    public function facebookAdvancedSearch(array $filter): array;
}
