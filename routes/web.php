<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    // return view('welcome');
    try {
        $dbConnected = false;
        $dbError = null;

        try {
            DB::connection()->getPdo();
            $dbConnected = true;
        } catch (\Exception $e) {
            $dbError = $e->getMessage();
        }

        $dbInfo = db_info();

        return response()->json([
            'status' => 'alive',
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'url' => config('app.url'),
                'timezone' => config('app.timezone'),
            ],
            'database' => array_merge([
                'connected' => $dbConnected,
                'error' => $dbError
            ], $dbInfo),
            'server' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'timestamp' => now()->toIso8601String(),
                'timezone' => date_default_timezone_get(),
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});
