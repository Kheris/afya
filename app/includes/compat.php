<?php
/**
 * Compatibility shim.
 *
 * Baadhi ya hosting za bure/shared (ikiwemo baadhi ya akaunti za InfinityFree)
 * hazina extension ya `mbstring`. Bila polyfill hizi, kurasa zinazotumia
 * mb_strtolower()/mb_substr()/mb_strpos() zinatoa "Fatal error: Call to
 * undefined function" - mfano api/chat.php na includes/articles.php.
 *
 * Polyfill hizi zinatumika TU kama extension halisi haipo.
 */

if (!function_exists('mb_strtolower')) {
    function mb_strtolower($string, $encoding = null) {
        // strtolower() haishughulikii herufi zenye alama (accents), lakini
        // kwa Kiswahili/Kiingereza cha kawaida inatosha kabisa.
        return strtolower((string)$string);
    }
}

if (!function_exists('mb_strtoupper')) {
    function mb_strtoupper($string, $encoding = null) {
        return strtoupper((string)$string);
    }
}

if (!function_exists('mb_strlen')) {
    function mb_strlen($string, $encoding = null) {
        // Hesabu herufi za UTF-8, si bytes.
        return strlen(preg_replace('/[\x80-\xBF]/', '', (string)$string));
    }
}

if (!function_exists('mb_substr')) {
    function mb_substr($string, $start, $length = null, $encoding = null) {
        $string = (string)$string;
        // Gawanya kwa herufi za UTF-8 ili tusikate herufi katikati.
        preg_match_all('/./us', $string, $matches);
        $chars = $matches[0] ?? [];
        $slice = ($length === null)
            ? array_slice($chars, $start)
            : array_slice($chars, $start, $length);
        return implode('', $slice);
    }
}

if (!function_exists('mb_strpos')) {
    function mb_strpos($haystack, $needle, $offset = 0, $encoding = null) {
        return strpos((string)$haystack, (string)$needle, $offset);
    }
}

if (!function_exists('mb_str_split')) {
    function mb_str_split($string, $length = 1, $encoding = null) {
        preg_match_all('/.{1,' . max(1, (int)$length) . '}/us', (string)$string, $matches);
        return $matches[0] ?? [];
    }
}
