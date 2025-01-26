<?php

namespace BIT;

use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

class BIT
{
    /**
     * Capture and store a baseline for the given key.
     *
     * @param string $key  The unique identifier for the baseline.
     * @param mixed  $data The data to store as the baseline.
     */
    public static function captureBaseline(string $key, $data): void
    {
        // Ensure the storage directory exists
        $storageDir = self::getStorageDirectory();
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        // Store the baseline data as a JSON file
        $filePath = self::getBaselineFilePath($key);
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Monitor the current behavior against the stored baseline.
     *
     * @param string $key  The unique identifier for the baseline.
     * @param mixed  $data The current data to compare against the baseline.
     * @return bool True if the current behavior matches the baseline, false otherwise.
     */
    public static function monitor(string $key, $data): bool
    {
        // Get the baseline data
        $filePath = self::getBaselineFilePath($key);
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Baseline not found for key: $key");
        }

        $baselineData = json_decode(file_get_contents($filePath), true);

        // Compare the current data to the baseline
        return $data === $baselineData;
    }

    /**
     * Detect and report drift between the current behavior and the baseline.
     *
     * @param string $key  The unique identifier for the baseline.
     * @param mixed  $data The current data to compare against the baseline.
     * @return array An array containing:
     *               - 'drift_detected' (bool): Whether drift was detected.
     *               - 'diff' (string|null): A detailed description of the differences (if any).
     */
    public static function detectDrift(string $key, $data): array
    {
        // Get the baseline data
        $filePath = self::getBaselineFilePath($key);
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Baseline not found for key: $key");
        }

        $baselineData = json_decode(file_get_contents($filePath), true);

        // Compare the current data to the baseline
        if ($data === $baselineData) {
            return [
                'drift_detected' => false,
                'diff' => null,
            ];
        }

        // Generate a detailed diff
        $differ = new Differ(new UnifiedDiffOutputBuilder("--- Baseline\n+++ Current\n"));
        $diff = $differ->diff(
            json_encode($baselineData, JSON_PRETTY_PRINT),
            json_encode($data, JSON_PRETTY_PRINT)
        );

        return [
            'drift_detected' => true,
            'diff' => $diff,
        ];
    }

    /**
     * Get detailed information about drift between the current behavior and the baseline.
     *
     * @param string $key  The unique identifier for the baseline.
     * @param mixed  $data The current data to compare against the baseline.
     * @return array An array containing:
     *               - 'drift_detected' (bool): Whether drift was detected.
     *               - 'summary' (string): A summary of the drift (e.g., "2 fields added, 1 field modified").
     *               - 'details' (array): A list of specific differences.
     */
    public static function getDriftDetails(string $key, $data): array
    {
        // Get the baseline data
        $filePath = self::getBaselineFilePath($key);
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Baseline not found for key: $key");
        }

        $baselineData = json_decode(file_get_contents($filePath), true);

        // Compare the current data to the baseline
        if ($data === $baselineData) {
            return [
                'drift_detected' => false,
                'summary' => 'No drift detected.',
                'details' => [],
            ];
        }

        // Recursively compare the arrays
        $changes = self::compareArrays($baselineData, $data);

        // Generate a summary using the generateSummary method
        $summary = self::generateSummary($changes);

        // Format the details
        $details = [];
        foreach ($changes['added'] as $key => $value) {
            $details[] = ['type' => 'added', 'field' => $key, 'value' => $value];
        }
        foreach ($changes['removed'] as $key => $value) {
            $details[] = ['type' => 'removed', 'field' => $key, 'value' => $value];
        }
        foreach ($changes['modified'] as $key => $value) {
            $details[] = ['type' => 'modified', 'field' => $key, 'value' => $value];
        }

        return [
            'drift_detected' => true,
            'summary' => $summary,
            'details' => $details,
        ];
    }

    /**
     * Parse the diff output into a structured array of differences.
     *
     * @param string $diff The diff output.
     * @return array An array of differences.
     */
    private static function parseDiff(string $diff): array
    {
        $lines = explode("\n", $diff);
        $details = [];
        $currentChange = null;

        foreach ($lines as $line) {
            if (str_starts_with($line, '+')) {
                // Added field
                $details[] = ['type' => 'added', 'value' => substr($line, 1)];
            } elseif (str_starts_with($line, '-')) {
                // Removed field
                $details[] = ['type' => 'removed', 'value' => substr($line, 1)];
            }
        }

        return $details;
    }

    /**
     * Generate a summary of the differences.
     *
     * @param array $changes The list of changes (added, removed, modified).
     * @return string A summary of the drift.
     */
    private static function generateSummary(array $changes): string
    {
        $parts = [];

        if (count($changes['added']) > 0) {
            $parts[] = count($changes['added']) . ' fields added';
        }
        if (count($changes['removed']) > 0) {
            $parts[] = count($changes['removed']) . ' fields removed';
        }
        if (count($changes['modified']) > 0) {
            $parts[] = count($changes['modified']) . ' fields modified';
        }

        if (empty($parts)) {
            return 'No drift detected.';
        }

        return implode(', ', $parts) . '.';
    }

    /**
     * Get the storage directory for baselines.
     *
     * @return string
     */
    public static function getStorageDirectory(): string
    {
        return __DIR__ . '/../../storage/baselines';
    }

    /**
     * Get the file path for a baseline.
     *
     * @param string $key The unique identifier for the baseline.
     * @return string
     */
    public static function getBaselineFilePath(string $key): string
    {
        return self::getStorageDirectory() . '/' . $key . '.json';
    }

    /**
     * Recursively compare two arrays and identify changes.
     *
     * @param array $baseline The baseline data.
     * @param array $current  The current data.
     * @return array An array containing:
     *               - 'added' (array): List of added fields.
     *               - 'removed' (array): List of removed fields.
     *               - 'modified' (array): List of modified fields.
     */
    private static function compareArrays(array $baseline, array $current): array
    {
        $added = [];
        $removed = [];
        $modified = [];

        // Check for added fields
        foreach ($current as $key => $value) {
            if (!array_key_exists($key, $baseline)) {
                $added[$key] = $value;
            } elseif (is_array($value) && is_array($baseline[$key])) {
                // Recursively compare nested arrays
                $nestedChanges = self::compareArrays($baseline[$key], $value);
                $added = array_merge($added, $nestedChanges['added']);
                $removed = array_merge($removed, $nestedChanges['removed']);
                $modified = array_merge($modified, $nestedChanges['modified']);
            } elseif ($value !== $baseline[$key]) {
                $modified[$key] = $value;
            }
        }

        // Check for removed fields
        foreach ($baseline as $key => $value) {
            if (!array_key_exists($key, $current)) {
                $removed[$key] = $value;
            }
        }

        return [
            'added' => $added,
            'removed' => $removed,
            'modified' => $modified,
        ];
    }
}