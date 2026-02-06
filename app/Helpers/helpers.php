<?php

use App\Models\Agency;
use App\Models\User;
use App\Models\Year;
use Illuminate\Support\Facades\Context;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

if (!function_exists('currentAgency')) {
    function currentAgency(): ?Agency
    {
        return Context::getHidden('current_agency');
    }
}

if (!function_exists('currentYear')) {
    function currentYear(): ?Year
    {
        return Context::getHidden('current_year');
    }
}

if (!function_exists('currentUser')) {
    function currentUser(): ?User
    {
        return request()->user();
    }
}

if (!function_exists('db_info')) {
    /**
     * Get database connection information
     * 
     * @return array
     */
    function db_info(): array
    {
        $connection = config('database.default');
        $host = config("database.connections.{$connection}.host");
        $database = config("database.connections.{$connection}.database");
        $username = config("database.connections.{$connection}.username");

        // Check based on APP_ENV instead of host
        $isProduction = config('app.env') === 'production';
        $environment = $isProduction ? 'PRODUCTION' : 'LOCAL';

        return [
            'environment' => $environment,
            'app_env' => config('app.env'),
            'host' => $host,
            'database' => $database,
            'username' => $username,
            'connection' => $connection,
            'is_local' => !$isProduction,
            'is_production' => $isProduction,
        ];
    }
}

if (!function_exists('perPage')) {
    function perPage()
    {
        $perPage = request('per_page');
        if (!is_numeric($perPage) || (int)$perPage <= 0) return 25;  // Validate: must be numeric, positive, and not zero
        return min((int)$perPage, 100); // Max limit 100
    }
}

if (!function_exists('uniqueInAgency')) {
    function uniqueInAgency(string $table, string $column, $ignore = null): Unique
    {
        // Note: This function uses the current agency
        return Rule::unique($table, $column)
            ->where('agency_id', currentAgency()->id)
            ->ignore($ignore);
    }
}
