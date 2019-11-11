<?php

if (3 !== $argc) {
    echo "USAGE: coverage-checker.php <TEXT-REPORT> <PERCENTAGE>\n";
    exit(1);
}

$inputFile = $argv[1];
$percentage = $argv[2];

if (!file_exists($inputFile)) {
    echo "Coverage file not found!\n";
    exit(1);
}

if (!is_numeric($percentage) || $percentage > 100 || $percentage < 0) {
    echo "Invalid coverage value: $percentage\n";
    exit(1);
}

if (!preg_match('/\n  Lines:\s+(\d+(?:\.\d+)?)%/', file_get_contents($inputFile), $vars)) {
    echo "Unable to parse text coverage report\n";
    exit(1);
}

$coverage = $vars[1];

echo "Actual: $coverage%\nExpected: $percentage%\n";
if ($coverage < $percentage) {
    echo "Coverage KO!\n";
    exit(2);
} else {
    echo "Coverage OK!\n";
}
