<?php

namespace App\Helpers;

class RandomHelper
{
    public static function randomString(int $length = 64): string
    {
        $items = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $itemsLength = strlen($items);
        $randomMax = $itemsLength - 1;

        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $items[rand(0, $randomMax)];
        }

        return $randomString;
    }
}
