<?php

namespace App\Services;

class Payload
{
    const SUPPORTED_PAYLOADS = [
        'iss',
        'exp',
        'iat',
    ];

    public static function generateAccessPayload($need): array
    {
        $lifeTime = env('ACCESS_LIFETIME');
        return self::generatePayload($need, $lifeTime);
    }

    public static function generateRefreshPayload($need): array
    {
        $lifeTime = env('REFRESH_LIFETIME');
        return self::generatePayload($need, $lifeTime);
    }

    private static function generatePayload($need, $lifeTime): array
    {
        $iat = self::iat();
        $payload = [
            'iss' => self::iss(),
            'iat' => self::iat(),
            'exp' => self::exp($iat, $lifeTime),
        ];

        foreach ($need as $item) {
            if (!isset($payload[$item]) && in_array($item, self::SUPPORTED_PAYLOADS)) {
                $payload[$item] = self::$item();
            }
        }

        return $payload;
    }

    public static function iss(): string
    {
        return (string)env('APP_URL', 'localhost');
    }

    private static function exp(int $iat, int $lifeTime): int
    {
        return $iat + $lifeTime;
    }

    private static function iat(): int
    {
        return time();
    }
}
