<?php

namespace Aspectox\LaraJoom\Core\Resilience;

use Throwable;
use Illuminate\Support\Facades\Log;

class SafeRunner
{
    /**
     * Executes any module, plugin, or component code in an isolated try-catch block.
     * Prevents third-party failures from halting the application execution.
     */
    public static function execute(string $extensionName, callable $callback, mixed $fallback = null): mixed
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            Log::error("[LaraJoom CircuitBreaker] Error in extension '{$extensionName}': " . $e->getMessage(), [
                'extension' => $extensionName,
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return is_callable($fallback) ? $fallback($e) : $fallback;
        }
    }
}
