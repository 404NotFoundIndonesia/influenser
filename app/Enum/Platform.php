<?php

namespace App\Enum;

enum Platform: string
{
    case TikTok = 'tiktok';
    case Instagram = 'instagram';
    case Facebook = 'facebook';
    case Twitter = 'twitter';
    case Pinterest = 'pinterest';
    case Youtube = 'youtube';
    case LinkedIn = 'linkedin';
    case Twitch = 'twitch';
    case Discord = 'discord';
    case Reddit = 'reddit';
    case Threads = 'threads';
    case Telegram = 'telegram';

    public static function values(): array
    {
        return array_map(fn (self $type) => $type->value , self::cases());
    }

    public function profileUrl(string $username): string
    {
        return match ($this) {
            self::TikTok => "https://www.tiktok.com/@{$username}",
            self::Instagram => "https://www.instagram.com/{$username}",
            self::Facebook => "https://www.facebook.com/{$username}",
            self::Twitter => "https://twitter.com/{$username}",
            self::Pinterest => "https://www.pinterest.com/{$username}",
            self::Youtube => "https://www.youtube.com/{$username}",
            self::LinkedIn => "https://www.linkedin.com/in/{$username}",
            self::Twitch => "https://www.twitch.tv/{$username}",
            self::Discord => "https://discord.com/users/{$username}",
            self::Reddit => "https://www.reddit.com/user/{$username}",
            self::Threads => "https://www.threads.net/@{$username}",
            self::Telegram => "https://t.me/{$username}",
        };
    }
}
