<?php

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
