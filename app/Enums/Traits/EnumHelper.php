<?php

namespace App\Enums\Traits;

trait EnumHelper
{
    public static function keys(): array
    {
        return array_column(static::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(static::cases(), 'value');
    }

    public static function valuesExcept(array $exclude = []): array
    {
        if (empty($exclude)) return self::values();

        $excludedValues = array_map(fn(self $case) => $case->value, $exclude);

        return array_values(array_diff(self::values(), $excludedValues));
    }
}
