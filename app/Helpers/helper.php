<?php

use Illuminate\Support\Facades\DB;

function isJSON($string)
{
    return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE);
}

function bulkUpdate($table, $values, $index = 'id')
{
    $final = [];
    $ids = [];

    if (!count($values)) return false;

    if (empty($index)) return false;

    $slots = [];
    foreach ($values as $key => $val) {
        $ids[] = $val[$index];
        foreach (array_keys($val) as $field) {
            if ($field !== $index) {
                $value = (is_null($val[$field]) ? NULL : $val[$field]);
                $slotKey = ':' . $field . '_' . $key;

                $slots[$slotKey] = $value;
                $final[$field][] = "WHEN `$index` = " . $val[$index] . " THEN " . $slotKey . " ";
            }
        }
    }

    $cases = '';
    foreach ($final as $k => $v) {
        $cases .= '`' . $k . '` = (CASE ' . implode("\n", $v) . "\n"
            . 'ELSE `' . $k . '` END), ';
    }

    $query = "UPDATE `$table` SET " . substr($cases, 0, -2) . " WHERE `$index` IN(" . implode(',', $ids) . ");";

    return DB::statement($query, $slots);
}

function getSubdomain($domain) {
    return data_get(explode('.', $domain), 0);
}

function getLocale($key, $languageCode, $data = []) {
    $translation = __($key, $data, $languageCode);
    return $translation !== $key ? $translation : null;
}
