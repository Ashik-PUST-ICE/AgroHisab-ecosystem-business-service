<?php

if (! function_exists('authId')) {
    function authId(): ?int
    {
        $claims = request()->attributes->get('jwt_claims');

        return $claims ? (int) ($claims['sub'] ?? null) : null;
    }
}

if (! function_exists('format_currency')) {
    function format_currency($value, int $decimals = 2): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $number = is_string($value) ? floatval($value) : $value;

        if (! is_numeric($number)) {
            return '';
        }

        $formatted = number_format($number, $decimals, '.', '');

        return '৳'.$formatted;
    }
}

if (! function_exists('parse_currency')) {
    function parse_currency(?string $formattedValue): float
    {
        if (! $formattedValue) {
            return 0;
        }

        $numericString = str_replace(['৳', ','], '', $formattedValue);

        return floatval($numericString);
    }
}

if (! function_exists('format_smart_number')) {
    function format_smart_number($value, bool $useThousandsSeparator = false): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $number = is_string($value) ? floatval($value) : $value;

        if (! is_numeric($number)) {
            return '';
        }

        $formatted = (intval($number) == $number) ? (string) intval($number) : (string) $number;

        if ($useThousandsSeparator) {
            $parts = explode('.', $formatted);
            $parts[0] = number_format(intval($parts[0]), 0, '', ',');
            $formatted = isset($parts[1]) ? $parts[0].'.'.$parts[1] : $parts[0];
        }

        return $formatted;
    }
}

if (! function_exists('parse_smart_number')) {
    function parse_smart_number(?string $value): float
    {
        if (! $value) {
            return 0;
        }

        $numericString = str_replace(',', '', $value);

        return floatval($numericString);
    }
}
