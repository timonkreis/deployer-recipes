<?php
declare(strict_types=1);

namespace Deployer;

require dirname(__DIR__) . '/src/functions.php';

set('branch', 'main');
set('git_tty', true);
set('writable_dirs', []);
set('keep_releases', 3);
set('writable_mode', 'chmod');

set('repository', function() {
    if (@is_file(project_root() . '/.git/config')) {
        $data = parse_ini_file(project_root() . '/.git/config', true);

        if (isset($data['remote origin']['url'])) {
            return $data['remote origin']['url'];
        }
    }

    return '';
});

desc('Download .env');
task('download:env', function(): void {
    if (!askConfirmation('Do you want to download the file ".env"?', true)) {
        return;
    }

    $name = @is_file(project_root() . '/.env')
        ? ask('The file already exists. Do you want to rename the downloaded file?', '.env')
        : '.env';

    download('{{current_path}}/.env', $name, ['flags' => '-azLP']);
});

desc('Download auth.json');
task('download:auth.json', function(): void {
    if (!askConfirmation('Do you want to download the file "auth.json"?', true)) {
        return;
    }

    $name = @is_file(project_root() . '/auth.json')
        ? ask('The file already exists. Do you want to rename the downloaded file?', 'auth.json')
        : 'auth.json';

    download('{{current_path}}/auth.json', $name, ['flags' => '-azLP']);
});

after('deploy:failed', 'deploy:unlock');
