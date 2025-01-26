<?php

namespace BIT\Tests;

use BIT\BIT;
use PHPUnit\Framework\TestCase;

class BITTest extends TestCase
{
    public function testBaselineCapture()
    {
        $response = ['status' => 'success'];
        BIT::captureBaseline('test-response', $response);

        // Verify that the baseline file was created
        $filePath = __DIR__ . '/../storage/baselines/test-response.json';
        $this->assertFileExists($filePath);

        // Verify the content of the baseline file
        $storedData = json_decode(file_get_contents($filePath), true);
        $this->assertEquals($response, $storedData);
    }

    public function testMonitorNoDrift()
    {
        $response = ['status' => 'success'];
        BIT::captureBaseline('test-response', $response);

        // Monitor the same response (no drift)
        $this->assertTrue(BIT::monitor('test-response', $response));
    }

    public function testMonitorWithDrift()
    {
        $response = ['status' => 'success'];
        BIT::captureBaseline('test-response', $response);

        // Monitor a different response (drift detected)
        $differentResponse = ['status' => 'failure'];
        $this->assertFalse(BIT::monitor('test-response', $differentResponse));
    }

    public function testDetectDriftNoDrift()
    {
        $response = ['status' => 'success'];
        BIT::captureBaseline('test-response', $response);

        // Detect drift with the same response (no drift)
        $result = BIT::detectDrift('test-response', $response);
        $this->assertFalse($result['drift_detected']);
        $this->assertNull($result['diff']);
    }

    public function testDetectDriftWithDrift()
    {
        $response = ['status' => 'success'];
        BIT::captureBaseline('test-response', $response);

        // Detect drift with a different response
        $differentResponse = ['status' => 'failure'];
        $result = BIT::detectDrift('test-response', $differentResponse);
        $this->assertTrue($result['drift_detected']);
        $this->assertStringContainsString('+    "status": "failure"', $result['diff']);
        $this->assertStringContainsString('-    "status": "success"', $result['diff']);
    }

    public function testGetDriftDetailsNoDrift()
    {
        $response = ['status' => 'success'];
        BIT::captureBaseline('test-response', $response);

        // Get drift details with the same response (no drift)
        $result = BIT::getDriftDetails('test-response', $response);
        $this->assertFalse($result['drift_detected']);
        $this->assertEquals('No drift detected.', $result['summary']);
        $this->assertEmpty($result['details']);
    }

    public function testGetDriftDetailsWithDrift()
    {
        $response = ['status' => 'success'];
        BIT::captureBaseline('test-response', $response);
    
        // Get drift details with a different response
        $differentResponse = ['status' => 'failure', 'new_field' => 'value'];
        $result = BIT::getDriftDetails('test-response', $differentResponse);
        $this->assertTrue($result['drift_detected']);
        $this->assertEquals('1 fields added, 1 fields modified.', $result['summary']);
        $this->assertCount(2, $result['details']);
    }
}