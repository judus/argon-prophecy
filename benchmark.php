<?php

declare(strict_types=1);

/**
 * This script benchmarks the performance of a web server by sending multiple concurrent requests.
 * It uses cURL to perform the requests and measures various timing metrics.
 *
 * Usage:
 * php benchmark.php <target_url> <concurrency> <requests>
 *
 * Example:
 * php benchmark.php http://
 */


$target = $argv[1] ?? 'http://127.0.0.1:9501/';
$concurrency = (int)($argv[2] ?? 100);
$requests = (int)($argv[3] ?? 1000);

$multi = curl_multi_init();
$handles = [];
$results = [];

$start = microtime(true);

// Initialize all handles
for ($i = 0; $i < $concurrency; $i++) {
    $ch = curl_init($target);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_CONNECTTIMEOUT_MS => 1000,
        CURLOPT_TIMEOUT_MS => 2000,
    ]);
    $handles[] = $ch;
    curl_multi_add_handle($multi, $ch);
}

$completed = 0;

do {
    $status = curl_multi_exec($multi, $running);
    curl_multi_select($multi);

    while ($info = curl_multi_info_read($multi)) {
        $handle = $info['handle'];
        $totalTime = curl_getinfo($handle, CURLINFO_TOTAL_TIME);
        $startTransfer = curl_getinfo($handle, CURLINFO_STARTTRANSFER_TIME);
        $connectTime = curl_getinfo($handle, CURLINFO_CONNECT_TIME);

        $results[] = [
            'total' => $totalTime * 1000,
            'start_transfer' => $startTransfer * 1000,
            'connect' => $connectTime * 1000,
        ];

        curl_multi_remove_handle($multi, $handle);
        curl_close($handle);
        $completed++;

        if ($completed < $requests) {
            $ch = curl_init($target);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_CONNECTTIMEOUT_MS => 1000,
                CURLOPT_TIMEOUT_MS => 2000,
            ]);
            $handles[] = $ch;
            curl_multi_add_handle($multi, $ch);
        }
    }
} while ($running || $completed < $requests);

$duration = microtime(true) - $start;
curl_multi_close($multi);

// Stats
$totals = array_column($results, 'total');
sort($totals);
$avg = array_sum($totals) / count($totals);

echo "Benchmark results for {$requests} requests to {$target}:\n";
echo "Concurrency: {$concurrency}\n";
echo "Total time: " . round($duration, 2) . " sec\n";
echo "Average per request: " . round($avg, 2) . " ms\n";
echo "Median: " . round($totals[intdiv(count($totals), 2)], 2) . " ms\n";
echo "95th percentile: " . round($totals[(int)(count($totals) * 0.95)], 2) . " ms\n";
echo "Fastest: " . round(min($totals), 2) . " ms\n";
echo "Slowest: " . round(max($totals), 2) . " ms\n";
