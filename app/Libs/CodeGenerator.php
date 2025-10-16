<?php
namespace App\Libs;

class CodeGenerator {
    public static function generate(string $prefix): string {
        $uniquePart = strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 5));
        $randomDigit = mt_rand(0, 9);
        return sprintf('%s-%s-%d', $prefix, $uniquePart, $randomDigit);
    }
}


