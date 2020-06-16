<?php

namespace App\Databases;

use Illuminate\Support\Str;

class Sluggifier
{
    const DATABASE_LIMIT = 24;
    const USERNAME_LIMIT = 16;

    public static function username($str)
    {
        return self::slug($str, self::USERNAME_LIMIT);
    }

    public static function database($str)
    {
        return self::slug($str, self::DATABASE_LIMIT);
    }

    private static function slug($str, $limit)
    {
        return Str::limit(
            Str::slug(
                str_replace('.', '_', $str),
                '_'
            ),
            $limit
        );
    }
}