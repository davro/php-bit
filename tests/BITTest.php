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

    // test case that captures the composer.json file and checks the current php version
    public function testComposerJsonPhpVersion()
    {
        // Path to the composer.json file
        $composerJsonPath = __DIR__ . '/../composer.json';
    
        // Capture the current PHP version requirement as a baseline
        $composerJsonContent = file_get_contents($composerJsonPath);
        $composerJson = json_decode($composerJsonContent, true);
        $phpVersion = $composerJson['require']['php'] ?? null;
    
        //$this->assertNotNull($phpVersion, 'PHP version is not specified in composer.json.');
    
        BIT::captureBaseline('composer-php-version', ['php' => $phpVersion]);
    
        // Simulate a change to the PHP version requirement
        $modifiedComposerJson = $composerJson;
        $modifiedComposerJson['require']['php'] = '^8.2'; // Example change
    
        // Detect drift between the original and modified PHP version requirement
        $result = BIT::getDriftDetails('composer-php-version', ['php' => $modifiedComposerJson['require']['php']]);
    
        // Assert that drift is detected
        $this->assertTrue($result['drift_detected']);
    
        // Assert that the summary and details are as expected
        $this->assertStringContainsString('1 fields modified', $result['summary']);
        $this->assertCount(1, $result['details']);
    
        // Check the details of the drift
        $this->assertEquals('modified', $result['details'][0]['type']);
        $this->assertEquals('php', $result['details'][0]['field']);
        $this->assertEquals('^8.2', $result['details'][0]['value']);
    }

    // test case that captures the composer.json file and manages any drift is a great way to demonstrate the power of your Behavioral Integrity Testing (BIT) library. 
    public function testComposerJsonDrift()
    {
        // Path to the composer.json file
        $composerJsonPath = __DIR__ . '/../composer.json';
    
        // Capture the current composer.json as a baseline
        $composerJsonContent = file_get_contents($composerJsonPath);
        BIT::captureBaseline('composer-php-version', json_decode($composerJsonContent, true));
    
        // Check the original php composer.json
        $modifiedComposerJson = json_decode($composerJsonContent, true);

        // Simulate a change to the composer.json file
        $modifiedComposerJson['require']['php'] = '^8.1'; // Example change
    
        // Detect drift between the original and modified composer.json
        $result = BIT::getDriftDetails('composer-php-version', $modifiedComposerJson);
    
        // Assert that drift is detected
        $this->assertTrue($result['drift_detected']);
    
        // Assert that the summary and details are as expected
        $this->assertStringContainsString('1 fields modified', $result['summary']);
        $this->assertCount(1, $result['details']);
    
        // Check the details of the drift
        $this->assertEquals('modified', $result['details'][0]['type']);
        $this->assertEquals('php', $result['details'][0]['field']); // The modified field is 'php'
        $this->assertEquals('^8.1', $result['details'][0]['value']); // The new value is '^8.1'
    }
}
