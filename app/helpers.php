<?php

if (!function_exists('isPhoneOrEmail')) {
    function isPhoneOrEmail(string $value): string|bool
    {
        $value = trim($value);
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) return 'email';

        $value = preg_replace('/[^0-9]/', '', $value);
        if (str_starts_with($value, '0098')) $value = substr($value, 4);
        if (str_starts_with($value, '98') && strlen($value) > 10) $value = substr($value, 2);
        if (strlen($value) === 10 && str_starts_with($value, '9')) $value = '0' . $value;

        if (preg_match('/^09[0-9]{9}$/', $value)) return 'phone';

        return false;
    }
}

if (!function_exists('normalizePhoneNumber')) {
    function normalizePhoneNumber(string $value): string
    {
        $phone = preg_replace('/[^0-9]/', '', $value);
        if (str_starts_with($phone, '0098')) $phone = substr($phone, 4);
        if (str_starts_with($phone, '98') && strlen($phone) > 10) $phone = substr($phone, 2);
        if (strlen($phone) === 10 && str_starts_with($phone, '9')) $phone = '0' . $phone;
        if (strlen($phone) === 11 && str_starts_with($phone, '09')) return $phone;
        return $value;
    }
}

if (!function_exists('enNumber')) {
    function enNumber($string) {
        $newNumbers = range(0, 9);
        // 1. Persian HTML decimal
        $persianDecimal = array('&#1776;', '&#1777;', '&#1778;', '&#1779;', '&#1780;', '&#1781;', '&#1782;', '&#1783;', '&#1784;', '&#1785;');
        // 2. Arabic HTML decimal
        $arabicDecimal = array('&#1632;', '&#1633;', '&#1634;', '&#1635;', '&#1636;', '&#1637;', '&#1638;', '&#1639;', '&#1640;', '&#1641;');
        // 3. Arabic Numeric
        $arabic = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
        // 4. Persian Numeric
        $persian = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');

        $string =  str_replace($persianDecimal, $newNumbers, $string);
        $string =  str_replace($arabicDecimal, $newNumbers, $string);
        $string =  str_replace($arabic, $newNumbers, $string);
        return str_replace($persian, $newNumbers, $string);
    }
}

if (!function_exists('generateDashboardAuthKey')) {
    function generateDashboardAuthKey(): string
    {
        $length = (int) env('AUTHKEY_LENGTH', 16);

        return \Illuminate\Support\Str::random(max(8, $length));
    }
}
