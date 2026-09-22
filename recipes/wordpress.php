<?php
declare(strict_types=1);

namespace Deployer;

require_once 'recipe/wordpress.php';
require_once __DIR__ . '/common.php';

set('docroot', static function(): string {
    try {
        $json = load_json_from_file('composer.json');

        if (isset($json['extra']['wordpress-install-dir'])) {
            return dirname($json['extra']['wordpress-install-dir']);
        }
    } catch (\Throwable $e) {
        warning($e->getMessage());
    }

    return 'public';
});

set('wp_webroot', static function(): string {
    warning('Using "wp_webroot" is deprecated. Use "docroot" instead.');

    return get('docroot');
});

set('writable_dirs', ['{{docroot}}/app/uploads']);

set('shared_dirs', ['{{docroot}}/app/uploads']);

set('shared_files', static function(): array {
    $sharedFiles = [];
    $possibleFiles = [
        '.htninja',
        'auth.json',
        'wordpress-config.php',
        '{{docroot}}/.htaccess',
        '{{docroot}}/app/wp-cache-config.php',
    ];

    foreach ($possibleFiles as $possibleFile) {
        if (@is_file(project_root() . '/' . parse($possibleFile))) {
            $sharedFiles[] = $possibleFile;
        }
    }

    return $sharedFiles;
});

desc('Download uploads folder');
task('download:uploads', static function(): void {
    if (!askConfirmation('Do you want to download the uploads folder?', true)) {
        return;
    }

    $name = @is_dir(project_root() . parse('/{{docroot}}/app/uploads'))
        ? ask('The folder already exists. Do you want to rename the downloaded folder?', 'uploads')
        : 'uploads';

    download(
        '{{current_path}}/{{docroot}}/app/uploads/',
        '{{docroot}}/app/' . $name,
        ['flags' => '-azLP', 'options' => ['--delete']],
    );
});

task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'deploy:publish',
]);
