<?php

namespace BIT\Tests;

use BIT\BIT;
use BIT\Performance\Profiler;
use PHPUnit\Framework\TestCase;

class BITPerformanceProfilerTest extends TestCase 
{
    public function testPerformanceProfilerCapturesExecutionTime()
    {
        // Start timing
        Profiler::start('test_operation');

        // Simulate a small delay
        usleep(50000); // 50ms delay

        // Stop timing
        $executionTime = Profiler::stop('test_operation');

        // Assert that execution time is greater than 0
        $this->assertGreaterThan(0, $executionTime, "Execution time should be greater than 0");

        // Assert execution time is close to 50ms (±10ms tolerance)
        $this->assertGreaterThanOrEqual(0.04, $executionTime, "Execution time should be at least 40ms");
        $this->assertLessThanOrEqual(0.06, $executionTime, "Execution time should be at most 60ms");

        // Retrieve stored execution times
        $executionTimes = Profiler::getExecutionTimes();
        $this->assertArrayHasKey('test_operation', $executionTimes, "Execution time should be stored");
        $this->assertEquals($executionTime, $executionTimes['test_operation'], "Stored execution time should match");
    }

    public function testPerformanceProfilerThrowsExceptionForMissingStart()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("No matching start() call for 'missing_operation'");

        // Stop timing without starting
        Profiler::stop('missing_operation');
    }

    public function testProfilerHandlesMultipleOperations() {
        Profiler::start('operation_1');
        usleep(10000); // 10ms delay simulation
        Profiler::stop('operation_1');
    
        Profiler::start('operation_2');
        usleep(20000); // 20ms delay simulation
        Profiler::stop('operation_2');
    
        $executionTimes = Profiler::getExecutionTimes();
    
        $this->assertArrayHasKey('operation_1', $executionTimes);
        $this->assertArrayHasKey('operation_2', $executionTimes);
    
        $this->assertGreaterThanOrEqual(0.005, $executionTimes['operation_1']);
        $this->assertLessThanOrEqual(0.02, $executionTimes['operation_1']);
    
        $this->assertGreaterThanOrEqual(0.01, $executionTimes['operation_2']);
        $this->assertLessThanOrEqual(0.03, $executionTimes['operation_2']);
    }

    public function testProfilerHandlesRepeatedOperations() {
        Profiler::start('repeated_operation');
        usleep(30000); // 30ms
        Profiler::stop('repeated_operation');
    
        Profiler::start('repeated_operation'); // Start again
        usleep(10000); // 10ms
        Profiler::stop('repeated_operation');
    
        $executionTimes = Profiler::getExecutionTimes();
    
        $this->assertGreaterThanOrEqual(0.005, $executionTimes['repeated_operation']);
        $this->assertLessThanOrEqual(0.02, $executionTimes['repeated_operation']);
    }
}
