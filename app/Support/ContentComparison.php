<?php

namespace App\Support;

class ContentComparison
{
    public static function same(mixed $left, mixed $right): bool
    {
        return self::normalize($left) === self::normalize($right);
    }

    private static function normalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value === '' ? null : $value;
        }
        if (array_is_list($value)) {
            return array_map(self::normalize(...), $value);
        }
        $result = [];
        foreach ($value as $key => $item) {
            $item = self::normalize($item);
            if ($item !== null) {
                $result[$key] = $item;
            }
        }
        ksort($result);

        return $result;
    }
}
