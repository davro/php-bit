<?php

namespace BIT\Performance;

class Profiler 
{
    private static array $captureStartTime = [];
    private static array $captureEndTime = [];
    private static array $executionTimes = [];

    public static function start(string $name): void {
        self::$captureStartTime[$name] = microtime(true);
    }

    public static function stop(string $name): float {
        if (!isset(self::$captureStartTime[$name])) {
            throw new \Exception("No matching start() call for '$name'");
        }

        self::$captureEndTime[$name] = microtime(true);
        $executionTime = self::$captureEndTime[$name] - self::$captureStartTime[$name];

        // Store execution time
        self::$executionTimes[$name] = $executionTime;

        // Return execution time
        return $executionTime;
    }

    public static function getExecutionTimes(): array {
        return self::$executionTimes;
    }
}