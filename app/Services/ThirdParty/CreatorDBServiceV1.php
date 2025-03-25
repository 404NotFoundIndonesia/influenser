<?php

namespace App\Services\ThirdParty;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class CreatorDBServiceV1 implements CreatorDBServiceInterface
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    private function url(string $endpoint): string
    {
        return sprintf("%s/%s", $this->baseUrl, $endpoint);
    }

    private function headers(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'apiId' => $this->apiKey,
        ];
    }

    /**
     * Check API Status
     * [GET] /v2/apiStatus
     * https://creatordb.stoplight.io/docs/creatordb/2zgq0z731mvjr-check-api-status
     *
     * @throws ConnectionException
     */
    public function checkAPIStatus(): array
    {
        $endpoint = 'v2/apiStatus';
        $url = self::url($endpoint);

        $response = Http::withHeaders(self::headers())
            ->get($url);

        return json_decode($response->body(), true);
    }

    /**
     * TikTok Basic
     * [GET] /v2/tiktokBasic
     * https://creatordb.stoplight.io/docs/creatordb/17qhezdo76t6e-tik-tok-basic
     *
     * @throws ConnectionException
     */
    public function tiktokBasic(string $username): array
    {
        $endpoint = 'v2/tiktokBasic';
        $url = self::url($endpoint);

        $response = Http::withHeaders(self::headers())
            ->get($url, [
                'tiktokId' => $username,
            ]);

        return json_decode($response->body(), true);
    }

    /**
     * TikTok Basic + Historical
     * [GET] /v2/tiktokHistory
     * https://creatordb.stoplight.io/docs/creatordb/dua7t8c545fvo-tik-tok-basic-historical
     *
     * @throws ConnectionException
     */
    public function tiktokHistory(string $username): array
    {
        $endpoint = 'v2/tiktokHistory';
        $url = self::url($endpoint);

        $response = Http::withHeaders(self::headers())
            ->get($url, [
                'tiktokId' => $username,
            ]);

        return json_decode($response->body(), true);
    }

    /**
     * TikTok Advanced Search
     * [POST] /v2/tiktokAdvancedSearch
     * https://creatordb.stoplight.io/docs/creatordb/l54e38bh6atop-tik-tok-advanced-search
     *
     * Body Example:
     * {
     *  "maxResults": 3,
     *  "sortBy": "followers",
     *  "offset": 0,
     *  "desc": true,
     *  "filters": [
     *      {
     *          "filterKey": "followers",
     *          "op": ">",
     *          "value": 10000
     *      }
     *  ]
     * }
     *
     * Available filterKey:
     *  tiktokId, tiktokName, youtubeId, instagramId, isVerified, isPrivateAccount, hashtags, country, lang,
     *  followers, following, hearts, videos, avgLength, avgHearts, avgShares, avgComments, avgPlays,
     *  engageRate, gRateFollowers, gRateHearts, gRateAvgHearts, gRateAvgShares, gRateAvgComments,
     *  gRateAvgPlays, gRateEngageRate
     *
     * @throws ConnectionException
     */
    public function tiktokAdvancedSearch(array $filter): array
    {
        $endpoint = 'v2/tiktokAdvancedSearch';
        $url = self::url($endpoint);

        $response = Http::withHeaders(self::headers())
            ->post($url, $filter);

        return json_decode($response->body(), true);
    }

    /**
     * Instagram Basic
     * [GET] /v2/instagramBasic
     * https://creatordb.stoplight.io/docs/creatordb/g3fjuucqgzy7b-instagram-basic
     *
     * @throws ConnectionException
     */
    public function instagramBasic(string $username): array
    {
        $endpoint = 'v2/instagramBasic';
        $url = self::url($endpoint);

        $response = Http::withHeaders(self::headers())
            ->get($url, [
                'instagramId' => $username,
            ]);

        return json_decode($response->body(), true);
    }

    /**
     * Instagram Basic + Historical
     * [GET] /v2/instagramHistory
     * https://creatordb.stoplight.io/docs/creatordb/aidvekxqkyvaf-instagram-basic-historical
     *
     * @throws ConnectionException
     */
    public function instagramHistory(string $username): array
    {
        $endpoint = 'v2/instagramHistory';
        $url = self::url($endpoint);

        $response = Http::withHeaders(self::headers())
            ->get($url, [
                'instagramId' => $username,
            ]);

        return json_decode($response->body(), true);
    }

    /**
     * Instagram Advanced Search
     * [POST] /v2/instagramAdvancedSearch
     * https://creatordb.stoplight.io/docs/creatordb/oo9stlsrlooi8-instagram-advanced-search
     *
     * Body Example:
     * {
     *  "maxResults": 3,
     *  "sortBy": "followers",
     *  "offset": 0,
     *  "desc": true,
     *  "filters": [
     *      {
     *          "filterKey": "followers",
     *          "op": ">",
     *          "value": 10000
     *      }
     *  ]
     * }
     *
     * Available filterKey:
     *  instagramId, instagramName, youtubeId, category, categoryBusiness, isVerified, isBusinessAccount, hashtags,
     *  following, followers, posts, engageRate, avgLikes, avgComments, country, gRateFollowers, gRateLikes,
     *  gRateComments, gRateAvgLikes, gRateAvgComments, gRateEngageRate, lastPublishTime, dbUpdateTime
     *
     * @throws ConnectionException
     */
    public function instagramAdvancedSearch(array $filter): array
    {
        $endpoint = 'v2/instagramAdvancedSearch';
        $url = self::url($endpoint);

        $response = Http::withHeaders(self::headers())
            ->post($url, $filter);

        return json_decode($response->body(), true);
    }

    /**
     * YouTube Basic
     * [GET] /v2/youtubeBasic
     * https://creatordb.stoplight.io/docs/creatordb/09z47spvdqqcj-you-tube-basic
     *
     * @throws ConnectionException
     */
    public function youtubeBasic(string $username): array
    {
        $endpoint = 'v2/youtubeBasic';
        $url = self::url($endpoint);

        $response = Http::withHeaders(self::headers())
            ->get($url, [
                'youtubeId' => $username,
            ]);

        return json_decode($response->body(), true);
    }

    /**
     * YouTube Basic + Historical
     * [GET] /v2/youtubeHistory
     * https://creatordb.stoplight.io/docs/creatordb/s8brwtc73u04n-you-tube-basic-historical
     *
     * @throws ConnectionException
     */
    public function youtubeHistory(string $username): array
    {
        $endpoint = 'v2/youtubeHistory';
        $url = self::url($endpoint);

        $response = Http::withHeaders(self::headers())
            ->get($url, [
                'youtubeId' => $username,
            ]);

        return json_decode($response->body(), true);
    }

    /**
     * YouTube Detail
     * [GET] /v2/youtubeDetail
     * https://creatordb.stoplight.io/docs/creatordb/vzkfsgb6w9war-you-tube-detailed
     *
     * @throws ConnectionException
     */
    public function youtubeDetail(string $username): array
    {
        $endpoint = 'v2/youtubeDetail';
        $url = self::url($endpoint);

        $response = Http::withHeaders(self::headers())
            ->get($url, [
                'youtubeId' => $username,
            ]);

        return json_decode($response->body(), true);
    }

    /**
     * YouTube Contact/Email
     * [GET] /v2/youtubeEmail
     * https://creatordb.stoplight.io/docs/creatordb/nghw050e8oi88-influencer-contact-email
     *
     * @throws ConnectionException
     */
    public function youtubeEmail(string $username): array
    {
        $endpoint = 'v2/youtubeEmail';
        $url = self::url($endpoint);

        $response = Http::withHeaders(self::headers())
            ->get($url, [
                'youtubeId' => $username,
            ]);

        return json_decode($response->body(), true);
    }

    /**
     * YouTube Advanced Search
     * [POST] /v2/youtubeAdvancedSearch
     * https://creatordb.stoplight.io/docs/creatordb/1vapt131t9c96-you-tube-advanced-search
     *
     * Body Example:
     * {
     *  "maxResults": 3,
     *  "sortBy": "subscribers",
     *  "offset": 0,
     *  "desc": true,
     *  "filters": [
     *      {
     *          "filterKey": "subscribers",
     *          "op": ">",
     *          "value": 10000
     *      }
     *  ]
     * }
     *
     * Available filterKey:
     *  subscribers, totalViews, hasBusinessEmail, hasEmail, country, lastVideoUploadTime, mainLanguage,
     *  mainLanguageRatio, creationDate, avgViews1Y, engageRate1Y, avgLikes1Y, avgDislikes1Y, avgComments1Y,
     *  avgSelfCommentRatio1Y, avgCommentLikeRatio1Y, avgCommentReplyRatio1Y, avgRating1Y, avgLength1Y, avgViewsR20,
     *  engageRateR20, avgLikesR20, avgDislikesR20, avgCommentsR20, avgRatingR20, avgSelfCommentRatioR20,
     *  avgCommentLikeRatioR20, avgCommentReplyRatioR20, avgLengthR20, mainCategory, mainCategoryRatio, topicIds,
     *  nicheIds, videosIn30Days, videosIn90Days, videos, ranking.score, ranking.engage, ranking.gEngage,
     *  ranking.avgViews, ranking.gAvgViews, ranking.subs, ranking.gSubs, demographic.mainCountry,
     *  demographic.mainCountryRatio, demographic.avgAge, demographic.genderFemaleRatio, demographic.genderMaleRatio,
     *  gSubscribers, gTotalViews, gAvgViewsR20, gEngageRateR20
     *
     * @throws ConnectionException
     */
    public function youtubeAdvancedSearch(array $filter): array
    {
        $endpoint = 'v2/youtubeAdvancedSearch';
        $url = self::url($endpoint);

        $response = Http::withHeaders(self::headers())
            ->post($url, $filter);

        return json_decode($response->body(), true);
    }

    /**
     * Facebook Basic
     * [GET] /v2/facebookBasic
     * https://creatordb.stoplight.io/docs/creatordb/56ffdab30e403-facebook-basic
     *
     * @throws ConnectionException
     */
    public function facebookBasic(string $username): array
    {
        $endpoint = 'v2/facebookBasic';
        $url = self::url($endpoint);

        $response = Http::withHeaders(self::headers())
            ->get($url, [
                'facebookId' => $username,
            ]);

        return json_decode($response->body(), true);
    }

    /**
     * Facebook Basic + Historical
     * [GET] /v2/facebookHistory
     * https://creatordb.stoplight.io/docs/creatordb/2cdf14a9d44db-facebook-basic-historical
     *
     * @throws ConnectionException
     */
    public function facebookHistory(string $username): array
    {
        $endpoint = 'v2/facebookHistory';
        $url = self::url($endpoint);

        $response = Http::withHeaders(self::headers())
            ->get($url, [
                'facebookId' => $username,
            ]);

        return json_decode($response->body(), true);
    }

    /**
     * Facebook Advanced Search
     * [POST] /v2/facebookAdvancedSearch
     * https://creatordb.stoplight.io/docs/creatordb/09bcc5a6a0d64-facebook-advanced-search
     *
     * Body Example:
     * {
     *  "maxResults": 3,
     *  "sortBy": "followers",
     *  "offset": 0,
     *  "desc": true,
     *  "filters": [
     *      {
     *          "filterKey": "followers",
     *          "op": ">",
     *          "value": 10000
     *      }
     *  ]
     * }
     *
     * Available filterKey:
     *  facebookId, facebookUserId, facebookPageId, facebookName, avatar, cover, isVerified, category, description,
     *  joinedDate, hasEmail, likes, followers, following, posts, reels, avgReactions, avgComments, avgShares, avgPlays,
     *  avgReactionsReels, avgCommentsReels, avgSharesReels, avgPlaysReels, lastUploadTime, dbUpdateTime,
     *  gRateAvgReactions, gRateAvgComments, gRateAvgShares, gRateAvgPlays, gRateAvgReactionsReels,
     *  gRateAvgCommentsReels, gRateAvgSharesReels, gRateAvgPlaysReels, gRateLikes, gRateFollowers, gRateFollowing,
     *  gRatePosts, gRateReels.
     *
     * @throws ConnectionException
     */
    public function facebookAdvancedSearch(array $filter): array
    {
        $endpoint = 'v2/facebookAdvancedSearch';
        $url = self::url($endpoint);

        $response = Http::withHeaders(self::headers())
            ->post($url, $filter);

        return json_decode($response->body(), true);
    }
}
