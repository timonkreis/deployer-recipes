<?php
declare(strict_types=1);

namespace Deployer;

require_once 'recipe/typo3.php';
require_once __DIR__ . '/common.php';

set('typo3_webroot', function(): string {
    try {
        $json = load_json_from_file('composer.json');

        if (isset($json['extra']['typo3/cms']['web-dir'])) {
            return $json['extra']['typo3/cms']['web-dir'];
        }
    } catch (\Exception $e) {}

    return 'public';
});

set('typo3_version', function(): int {
    try {
        $json = load_json_from_file('composer.lock');

        foreach ($json['packages'] as $package) {
            if ($package['name'] === 'typo3/cms-core') {
                $version = preg_replace('/[^\d.]/', '', $package['version']);
                $version = explode('.', $version, 1)[0];

                return (int)$version;
            }
        }
    } catch (\Exception $e) {}

    return 0;
});

set('writable_dirs', [
    '{{typo3_webroot}}/fileadmin',
    '{{typo3_webroot}}/typo3temp',
    'var',
]);

set('shared_dirs', [
    '{{typo3_webroot}}/fileadmin',
    '{{typo3_webroot}}/typo3temp',
    'var/charset',
    'var/lock',
    'var/log',
    'var/session',
]);

set('shared_files', function(): array {
    $sharedFiles = [];
    $possibleFiles = [
        '.env',
        'auth.json',
        'config/system/additional.php',
        '{{typo3_webroot}}/.htaccess',
        '{{typo3_webroot}}/typo3conf/AdditionalConfiguration.php',
    ];

    foreach ($possibleFiles as $possibleFile) {
        if (@is_file(project_root() . '/' . parse($possibleFile))) {
            $sharedFiles[] = $possibleFile;
        }
    }

    return $sharedFiles;
});

desc('Download fileadmin');
task('download:fileadmin', function(): void {
    if (!askConfirmation('Do you want to download the fileadmin folder?', true)) {
        return;
    }

    $name = @is_dir(project_root() . parse('/{{typo3_webroot}}/fileadmin'))
        ? ask('The folder already exists. Do you want to rename the downloaded folder?', 'fileadmin')
        : 'fileadmin';

    download(
        '{{current_path}}/{{typo3_webroot}}/fileadmin/',
        '{{typo3_webroot}}/' . $name,
        ['flags' => '-azLP', 'options' => ['--delete']],
    );
});
