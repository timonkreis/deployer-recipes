<?php
declare(strict_types=1);

namespace Deployer;

require_once 'recipe/typo3.php';
require_once __DIR__ . '/common.php';

set('docroot', static function(): string {
    try {
        $json = load_json_from_file('composer.json');

        if (isset($json['extra']['typo3/cms']['web-dir'])) {
            return $json['extra']['typo3/cms']['web-dir'];
        }
    } catch (\Throwable $e) {
        warning($e->getMessage());
    }

    return 'public';
});

set('typo3_webroot', static function(): string {
    warning('Using "typo3_webroot" is deprecated. Use "docroot" instead.');

    return get('docroot');
});

set('typo3_version', static function(): int {
    try {
        $json = load_json_from_file('composer.lock');

        foreach ($json['packages'] as $package) {
            if ($package['name'] === 'typo3/cms-core') {
                $version = preg_replace('/[^\d.]/', '', $package['version']);
                $version = explode('.', $version, 2)[0];

                return (int)$version;
            }
        }
    } catch (\Throwable $e) {
        warning($e->getMessage());
    }

    return 0;
});

set('writable_dirs', [
    '{{docroot}}/fileadmin',
    '{{docroot}}/typo3temp',
    'var',
]);

set('shared_dirs', [
    '{{docroot}}/fileadmin',
    '{{docroot}}/typo3temp',
    'var/charset',
    'var/lock',
    'var/log',
    'var/session',
]);

set('shared_files', static function(): array {
    $sharedFiles = [];
    $possibleFiles = [
        '.env',
        'auth.json',
        'config/system/additional.php',
        '{{docroot}}/.htaccess',
        '{{docroot}}/typo3conf/AdditionalConfiguration.php',
    ];

    foreach ($possibleFiles as $possibleFile) {
        if (@is_file(project_root() . '/' . parse($possibleFile))) {
            $sharedFiles[] = $possibleFile;
        }
    }

    return $sharedFiles;
});

desc('Download fileadmin folder');
task('download:fileadmin', static function(): void {
    if (!askConfirmation('Do you want to download the fileadmin folder?', true)) {
        return;
    }

    $name = @is_dir(project_root() . parse('/{{docroot}}/fileadmin'))
        ? ask('The folder already exists. Do you want to rename the downloaded folder?', 'fileadmin')
        : 'fileadmin';

    download(
        '{{current_path}}/{{docroot}}/fileadmin/',
        '{{docroot}}/' . $name,
        ['flags' => '-azLP', 'options' => ['--delete']],
    );
});
