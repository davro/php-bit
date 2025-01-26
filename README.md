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

```php
BIT::captureBaseline('test-response', $response);
```


## Capture a Baseline
Use the bit:capture command to capture a baseline of your system's behavior. The baseline will be stored as a JSON file in the storage/baselines/ directory.

```bash
php bin/bit bit:capture test-response '{"status":"success"}'
```

## Extract Drift Details
Use the bit:extract command to compare the current behavior against the stored baseline and extract drift details.

```bash
php bin/bit bit:extract test-response '{"status":"failure"}'
```

## Example Output
If drift is detected, the command will output a summary and details of the differences:

```bash
Drift Detected: true
Summary: 1 fields added, 1 fields modified.
Details:
- Added: new_field => value
- Modified: status => failure
```

## Using the Library in Code
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
Expand the CLI tool with additional commands, such as:

- `bit:list`: List all stored baselines.
- `bit:delete`: Delete a specific baseline.
- `bit:clear`: Clear all baselines.

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
