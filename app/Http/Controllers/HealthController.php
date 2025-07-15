<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Exception;

class HealthController extends Controller
{
    /**
     * Basic health check endpoint.
     */
    public function check(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'service' => config('app.name'),
            'version' => config('app.version', '1.0.0'),
        ]);
    }

    /**
     * Detailed health check with dependencies.
     */
    public function detailed(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'storage' => $this->checkStorage(),
        ];

        $overallStatus = collect($checks)->every(fn($check) => $check['status'] === 'healthy') 
            ? 'healthy' 
            : 'unhealthy';

        return response()->json([
            'status' => $overallStatus,
            'timestamp' => now()->toISOString(),
            'service' => config('app.name'),
            'version' => config('app.version', '1.0.0'),
            'checks' => $checks,
        ], $overallStatus === 'healthy' ? 200 : 503);
    }

    /**
     * Check database connectivity.
     */
    private function checkDatabase(): array
    {
        try {
            DB::select('SELECT 1');
            return [
                'status' => 'healthy',
                'message' => 'Database connection successful',
                'response_time' => $this->measureResponseTime(fn() => DB::select('SELECT 1'))
            ];
        } catch (Exception $e) {
            Log::error('Database health check failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'unhealthy',
                'message' => 'Database connection failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check cache system.
     */
    private function checkCache(): array
    {
        try {
            $key = 'health_check_' . time();
            $value = 'test_value';
            
            Cache::put($key, $value, 60);
            $retrieved = Cache::get($key);
            Cache::forget($key);
            
            if ($retrieved === $value) {
                return [
                    'status' => 'healthy',
                    'message' => 'Cache system working',
                    'response_time' => $this->measureResponseTime(fn() => Cache::get('test'))
                ];
            }
            
            return [
                'status' => 'unhealthy',
                'message' => 'Cache system not working properly'
            ];
        } catch (Exception $e) {
            Log::error('Cache health check failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'unhealthy',
                'message' => 'Cache system failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check queue system.
     */
    private function checkQueue(): array
    {
        try {
            // Check if we can connect to the queue
            $size = Queue::size();
            
            return [
                'status' => 'healthy',
                'message' => 'Queue system working',
                'queue_size' => $size,
                'failed_jobs' => $this->getFailedJobsCount()
            ];
        } catch (Exception $e) {
            Log::error('Queue health check failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'unhealthy',
                'message' => 'Queue system failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check storage system.
     */
    private function checkStorage(): array
    {
        try {
            $testFile = 'health_check_' . time() . '.txt';
            $testContent = 'health check test';
            
            // Test write
            file_put_contents(storage_path('app/' . $testFile), $testContent);
            
            // Test read
            $content = file_get_contents(storage_path('app/' . $testFile));
            
            // Clean up
            unlink(storage_path('app/' . $testFile));
            
            if ($content === $testContent) {
                return [
                    'status' => 'healthy',
                    'message' => 'Storage system working',
                    'writable' => is_writable(storage_path('app')),
                    'disk_space' => $this->getDiskSpace()
                ];
            }
            
            return [
                'status' => 'unhealthy',
                'message' => 'Storage system not working properly'
            ];
        } catch (Exception $e) {
            Log::error('Storage health check failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'unhealthy',
                'message' => 'Storage system failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Measure response time for a callable.
     */
    private function measureResponseTime(callable $callback): float
    {
        $start = microtime(true);
        $callback();
        $end = microtime(true);
        
        return round(($end - $start) * 1000, 2); // Convert to milliseconds
    }

    /**
     * Get failed jobs count.
     */
    private function getFailedJobsCount(): int
    {
        try {
            return DB::table('failed_jobs')->count();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get disk space information.
     */
    private function getDiskSpace(): array
    {
        $path = storage_path();
        return [
            'free' => $this->formatBytes(disk_free_space($path)),
            'total' => $this->formatBytes(disk_total_space($path)),
            'used_percentage' => round((1 - disk_free_space($path) / disk_total_space($path)) * 100, 2)
        ];
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
} 