<?php

namespace App\Helpers;

class NumberToWords
{
    protected static $ones = [
        '', 'one', 'two', 'three', 'four', 'five',
        'six', 'seven', 'eight', 'nine', 'ten',
        'eleven', 'twelve', 'thirteen', 'fourteen',
        'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'
    ];

    protected static $tens = [
        '', '', 'twenty', 'thirty', 'forty', 'fifty',
        'sixty', 'seventy', 'eighty', 'ninety'
    ];

    protected static $thousands = [
        '', 'thousand', 'million', 'billion', 'trillion'
    ];

    public static function convert($number): string
    {
        if ($number == 0) {
            return 'zero';
        }

        $number = round($number, 2); // 2 decimals
        $integerPart = floor($number);
        $decimalPart = ($number - $integerPart) * 100;

        $words = self::convertNumber($integerPart);

        if ($decimalPart > 0) {
            $words .= ' and ' . self::convertNumber($decimalPart) . ' cents';
        }

        return ucfirst(trim($words));
    }

    protected static function convertNumber($number): string
    {
        if ($number < 20) {
            return self::$ones[$number];
        } elseif ($number < 100) {
            return self::$tens[floor($number / 10)] . ' ' . self::$ones[$number % 10];
        } else {
            $words = '';
            $unit = 0;

            while ($number > 0) {
                $chunk = $number % 1000;
                if ($chunk) {
                    $words = self::convertChunk($chunk) . ' ' . self::$thousands[$unit] . ' ' . $words;
                }
                $number = floor($number / 1000);
                $unit++;
            }

            return trim($words);
        }
    }

    protected static function convertChunk($number): string
    {
        $words = '';

        if ($number > 99) {
            $words .= self::$ones[floor($number / 100)] . ' hundred ';
            $number %= 100;
        }

        if ($number > 0) {
            if ($number < 20) {
                $words .= self::$ones[$number];
            } else {
                $words .= self::$tens[floor($number / 10)];
                if ($number % 10) {
                    $words .= '-' . self::$ones[$number % 10];
                }
            }
        }

        return trim($words);
    }
}
