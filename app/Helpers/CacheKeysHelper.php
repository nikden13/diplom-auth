<?php

namespace App\Helpers;

class CacheKeysHelper
{
    public static function messageForSign(string $address): string
    {
        return 'message_for_sign_' . $address;
    }

    public static function twoFactorCode(string $address): string
    {
        return 'two_factor_code_' . $address;
    }

    public static function addressEmail(string $address): string
    {
        return 'email_' . $address;
    }
}
