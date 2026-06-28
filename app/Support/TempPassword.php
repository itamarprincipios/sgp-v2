<?php

namespace App\Support;

class TempPassword
{
    /**
     * Generates a random temporary password avoiding visually ambiguous
     * characters (0/O/o, 1/l/I) that are easily mistyped when a user
     * manually retypes a password shown on screen.
     */
    public static function generate(int $length = 10): string
    {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz';
        $max = strlen($chars) - 1;

        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $max)];
        }

        return $password;
    }
}
