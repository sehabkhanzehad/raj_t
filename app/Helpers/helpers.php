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

        $isLocal = in_array($host, ['127.0.0.1', 'localhost', '::1']);
        $environment = $isLocal ? 'LOCAL' : 'PRODUCTION';

        return [
            'environment' => $environment,
            'host' => $host,
            'database' => $database,
            'connection' => $connection,
            'is_local' => $isLocal,
            'is_production' => !$isLocal,
        ];
    }
}
