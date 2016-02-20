<?php
$projectdir = $argv[1];
$verbose = $argv[2] === '-v' || $argv[2] === '--verbose';

echo '--- Checking project ' . $projectdir . " ---\n";

$hasComposer = false;
if (file_exists($projectdir.'/composer.json')) {
    $hasComposer = true;
}
$hasNpm = false;
if (file_exists($projectdir.'/package.json')) {
    $hasNpm = true;
}

if ($hasComposer) {
    $vulns = shell_exec('/vagrant/vendor/bin/security-checker security:check --format=json ' . escapeshellarg($projectdir));
    $vulns = json_decode($vulns, true);

    echo 'SensioLabs Security Checker: ';
    if (count($vulns) === 0) {
        echo '0 vulnerable packages'."\n";
    } else {
        echo count($vulns) . ' vulnerable package(s)'."\n";
        foreach ($vulns as $k => $v) {
            if ($verbose) echo '- ' . $k . ': ' . $v['version'] . "\n";
        }
    }
}

if ($hasComposer) {
    echo 'Composer Climb: ';
    $updates = shell_exec('/vagrant/vendor/bin/climb outdated --directory='.escapeshellarg($projectdir).'');

    $packages = [];
    foreach (explode("\n", $updates) as $line) {
        if (strlen($line) > 1 && strpos($line, 'The following dependencies') === false) {
            $line = preg_replace('#[ ][ ]+#', ' ', $line);
            $parts = explode(' ', $line);
            $packages[$parts[0]] = $parts[1] . ' => ' . $parts[3];
        }
    }
    echo count($packages) . ' package(s) can be upgraded' . "\n";

    foreach ($packages as $p => $v) {
        if ($verbose) echo '- ' . $p . ': ' . $v . "\n";
    }
}

if ($hasNpm) {
    echo 'npm: ';
    $updates = shell_exec('cd '.escapeshellarg($projectdir).' && npm outdated --json');
    $updates = json_decode($updates, true);

    $packages = [];
    $localPackages = [];
    foreach ($updates as $k => $v) {
        if ($v['wanted'] !== $v['latest']) {
            $packages[$k] = $v['wanted'] . ' => ' . $v['latest'];
        }
        if ($v['current'] !== $v['wanted']) {
            $localPackages[$k] = $v['current'] . ' => ' . $v['wanted'];
        }
    }
    echo count($packages).' package(s) can be upgraded'."\n";
    ksort($packages);
    foreach ($packages as $k => $v) {
        if ($verbose) echo '- ' . $k.': '.$v."\n";
    }
    //echo 'npm: ';
    //echo count($localPackages).' local package(s) are outdated (do a clean npm install)'."\n";
    //ksort($localPackages);
    //foreach ($localPackages as $k => $v) {
    //    if ($verbose) echo '- ' . $k.': '.$v."\n";
    //}
}
