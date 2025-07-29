<?php
declare(strict_types=1);

namespace Deployer;

require_once 'recipe/wordpress.php';
require_once __DIR__ . '/common.php';

set('wp_webroot', function(): string {
    try {
        $json = load_json_from_file('composer.json');

        if (isset($json['extra']['wordpress-install-dir'])) {
            return dirname($json['extra']['wordpress-install-dir']);
        }
    } catch (\Exception $e) {}

    return 'public';
});

set('writable_dirs', ['{{wp_webroot}}/app/uploads']);

desc('Download uploads');
task('download:uploads', function(): void {
    if (!askConfirmation('Do you want to download the uploads folder?', true)) {
        return;
    }

    $name = @is_dir(project_root() . parse('/{{wp_webroot}}/app/uploads'))
        ? ask('The folder already exists. Do you want to rename the downloaded folder?', 'uploads')
        : 'uploads';

    download(
        '{{current_path}}/{{wp_webroot}}/app/uploads/',
        '{{wp_webroot}}/app/' . $name,
        ['flags' => '-azLP', 'options' => ['--delete']],
    );
});

task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'deploy:publish',
]);
