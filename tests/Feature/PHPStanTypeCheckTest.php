<?php

declare(strict_types=1);

namespace Glutamate\Tests\Feature;

it('verifies that PHPStan detects query type mismatches', function () {
    $phpstanPath = realpath(__DIR__.'/../../vendor/bin/phpstan');
    $configPath = realpath(__DIR__.'/../PHPStan/phpstan.neon');
    $filePath = realpath(__DIR__.'/../PHPStan/query-type-safety-should-fail.php');

    $command = sprintf(
        'php %s analyse %s --configuration=%s --error-format=json',
        escapeshellarg($phpstanPath),
        escapeshellarg($filePath),
        escapeshellarg($configPath),
    );

    exec($command, $outputLines, $resultCode);

    $output = implode("\n", $outputLines);
    $result = json_decode($output, true);

    expect($result)->toBeArray();

    // Normalize path keys in result
    $fileErrors = [];

    if (isset($result['files'])) {
        foreach ($result['files'] as $path => $data) {
            if (realpath($path) === $filePath) {
                $fileErrors = $data['messages'];

                break;
            }
        }
    } elseif (isset($result['error_details'])) {
        foreach ($result['error_details'] as $path => $errors) {
            if (realpath($path) === $filePath) {
                $fileErrors = $errors;

                break;
            }
        }
    }

    $errorMessages = array_map(fn ($err) => $err['message'], $fileErrors);

    expect($errorMessages)->toHaveCount(3);

    expect($errorMessages[0])->toContain('expects string, int given');
    expect($errorMessages[1])->toContain('expects int, string given');
    expect($errorMessages[2])->toContain('expects array of string');
    expect($errorMessages[2])->toContain('given');
});
