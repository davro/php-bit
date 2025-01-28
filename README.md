# Behavioral Integrity Testing (BIT) Library

The **Behavioral Integrity Testing (BIT)** library is a PHP tool designed to help developers detect and prevent behavioral drift in their applications. 
It allows you to capture a baseline of your system's behavior (e.g., API responses, database states) and monitor it over time to detect unintended changes.

## Features

- **Capture Baselines**: Store a snapshot of your system's behavior for future comparison.
- **Monitor Behavior**: Compare the current behavior against the stored baseline to detect drift.
- **Detect Drift**: Identify specific differences between the current behavior and the baseline.
- **CLI Support**: Use the command-line interface to capture baselines and extract drift details.

## Installation

You can install the BIT library via Composer:

```bash
composer require davro/php-bit
```

## Usage

To use the BIT library, you can capture a baseline response first. This is the response that you want to compare all future responses against.

## Code: Capture a Baseline with no Drift, monitor status returns true.
```php
$response = ['status' => 'success'];
BIT::captureBaseline('test-response', $response);

// Returns true if the response is the same as the baseline
$status = BIT::monitor('test-response', $response);
```

## Code: Capture a Baseline with Drift, monitor status returns false.
```php
$response = ['status' => 'success'];
BIT::captureBaseline('test-response', $response);

// Monitor a different response (drift detected)
$differentResponse = ['status' => 'failure'];
$status = BIT::monitor('test-response', $differentResponse);
```

## Code: Capture a Baseline with Drift, monitor status and fetch diff.
```php
$response = ['status' => 'success'];
BIT::captureBaseline('test-response', $response);

// Detect drift with a different response
$differentResponse = ['status' => 'failure'];
$result = BIT::detectDrift('test-response', $differentResponse);

// Returns true if drift is detected
$drift = $result['drift_detected'];

// Returns the diff between the baseline and the current response
$resultDiff = $result['diff'];

// Result Diff
// '+    "status": "failure"'
// '-    "status": "success"'
```


## Command Line: Capture a Baseline
Use the bit:capture command to capture a baseline of your system's behavior. The baseline will be stored as a JSON file in the storage/baselines/ directory.

```bash
php bin/bit bit:capture test-response '{"status":"success"}'
```

## Command Line: Extract Baseline Data.
If no data argument is provided, the command will return the stored baseline data:

```bash
php bin/bit bit:extract test-response
Stored Baseline:
{
    "status": "success"
}
```

## Command Line: Extract Details with Drift 
If drift is detected, the command will output a summary and details of the differences:
Use the bit:extract command to compare the current behavior against the stored baseline and extract drift details.

```bash
php bin/bit bit:extract test-response '{"status":"failure"}'
Drift Detected: true
Summary: 1 fields modified.
Details:
- Modified: status => "failure"
```

## Command Line: Extract Details with Drift and adding Additional fields
If drift is detected, the command will output a summary and details of the differences:
Use the bit:extract command to compare the current behavior against the stored baseline and extract drift details.

```bash
php bin/bit bit:extract test-response '{"status":"failure","new_field1":"new_value1", "new_field2":"new_value2"}'
Drift Detected: true
Summary: 2 fields added, 1 fields modified.
Details:
- Added: new_field1 => "new_value1"
- Added: new_field2 => "new_value2"
- Modified: status => "failure"
```

## Command Line: Delete a Baseline
```bash
php bin/bit bit:delete test-response
```

Output if successful:

```bash
Baseline deleted for key: test-response
```

Output if baseline not found:

```bash
<error>Baseline not found for key: test-response</error>
```

Explanation of the Code
DeleteCommand Class:

The command takes one argument: key (the baseline identifier).

It checks if the baseline file exists in the storage/baselines/ directory.

If the file exists, it deletes it using unlink().

If the file does not exist, it outputs an error message.

Error Handling:

The command handles cases where the baseline does not exist or the file cannot be deleted.



## Command Line: Clear All Baselines
```bash
php bin/bit bit:clear
```

Output if successful:

```bash
Cleared 3 baselines.
```

Output if no baselines exist:

```bash
No baselines found to clear.
```

Output if deletion fails:

```bash
<error>Failed to delete baseline: test-response.json</error>
```

Explanation of the Code
ClearCommand Class:

The command scans the storage/baselines/ directory for JSON files.

It deletes each file using unlink() and keeps track of the number of deleted files.

If no baselines are found, it outputs a message saying No baselines found to clear.

Error Handling:

The command handles cases where files cannot be deleted and outputs an error message for each failure.



## Code: Using the Library
You can also use the BIT library directly in your PHP code:

```php
use BIT\BIT;

// Capture a baseline
BIT::captureBaseline('test-response', ['status' => 'success']);

// Monitor behavior
$result = BIT::getDriftDetails('test-response', ['status' => 'failure', 'new_field' => 'value']);

if ($result['drift_detected']) {
    echo "Drift Detected: true\n";
    echo "Summary: " . $result['summary'] . "\n";
    echo "Details:\n";
    foreach ($result['details'] as $detail) {
        echo "- {$detail['type']}: {$detail['field']} => {$detail['value']}\n";
    }
}
```

## Configuration

By default, baselines are stored in the storage/baselines/ directory. You can customize the storage directory by setting the BIT_STORAGE_DIR environment variable:

```bash
export BIT_STORAGE_DIR=/path/to/your/storage
```

## Contributing

Contributions are welcome! If you'd like to contribute to the BIT library, please follow these steps:

Fork the repository.

Create a new branch for your feature or bugfix.

Submit a pull request with a detailed description of your changes.

## License

The BIT library is open-source software licensed under the MIT License.

## Roadmap

Here are some planned features for future releases:

- Custom Storage Backends: Support for databases, cloud storage, and in-memory storage.
- Advanced Diffing: Improved diffing for nested structures and contextual diffs.
- Framework Integration: Middleware and service providers for Laravel, Symfony, and Slim.
- Reporting and Visualization: Generate HTML reports and visualize drift trends.

## Support

If you encounter any issues or have questions, please open an issue on GitHub.


## Roadmap

1. Add More CLI Commands
Expand the CLI tool with additional commands.

2. Add Framework Integration
Make the library easier to use in popular PHP frameworks like Laravel, Symfony, and Slim by:

- Creating service providers or bundles.
- Adding middleware for automatic behavior monitoring in web applications.

3. Enhance Diffing and Reporting
Improve the drift detection and reporting capabilities by:

- Adding support for nested structures (e.g., arrays within arrays, objects within objects).
- Generating HTML reports with detailed diffs and summaries.
- Visualizing drift trends over time using graphs and charts.

4. Add Support for Custom Storage Backends
Allow users to store baselines in different backends, such as:

- Databases (e.g., MySQL, PostgreSQL).
- Cloud Storage (e.g., AWS S3, Google Cloud Storage).
- In-Memory Storage for short-lived use cases.

5. Write More Tests
Ensure the library is reliable by adding comprehensive tests for all features and edge cases. For example:

- Test the CLI commands (bit:capture, bit:extract).
- Test edge cases like invalid JSON input, missing baselines, and empty data.

6. Engage the Community
Share PHP BIT with the PHP community to get feedback and contributions:

- Post about it on forums like Reddit or Dev.to.
- Share it on social media (e.g., Twitter, LinkedIn).
- Submit it to PHP newsletters or blogs.
