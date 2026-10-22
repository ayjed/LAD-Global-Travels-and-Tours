<?php
declare(strict_types=1);

$testFiles = glob(__DIR__ . '/../tests/*Test.php');
sort($testFiles);

$totalPass = 0;
$totalFail = 0;
$suiteResults = [];

echo "====================================================================" . PHP_EOL;
echo "LAD GLOBAL TRAVELS — RUNNING COMPLETE TEST SUITE REGRESSION" . PHP_EOL;
echo "====================================================================" . PHP_EOL;

foreach ($testFiles as $file) {
    $filename = basename($file);
    $cmd = 'C:\\xampp\\php\\php.exe ' . escapeshellarg($file);
    $output = shell_exec($cmd) ?? '';
    
    if (preg_match('/(?:TEST\s+)?RESULTS:\s*(\d+)\s*Passed,\s*(\d+)\s*Failed/i', $output, $m)) {
        $passed = (int)$m[1];
        $failed = (int)$m[2];
        $totalPass += $passed;
        $totalFail += $failed;
        $suiteResults[] = sprintf("%-30s | %3d PASSED | %2d FAILED | %s", $filename, $passed, $failed, $failed === 0 ? 'OK' : 'FAIL');
    } else {
        $suiteResults[] = sprintf("%-30s | ERROR RUNNING SUITE", $filename);
        $totalFail++;
    }
}

foreach ($suiteResults as $line) {
    echo $line . PHP_EOL;
}

echo "====================================================================" . PHP_EOL;
printf("TOTAL SYSTEM TESTS: %d PASSED, %d FAILED (Pass Rate: %0.1f%%)\n", $totalPass, $totalFail, $totalPass / max(1, ($totalPass + $totalFail)) * 100);
echo "====================================================================" . PHP_EOL;

exit($totalFail === 0 ? 0 : 1);
