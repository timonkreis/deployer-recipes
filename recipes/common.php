<?php
declare(strict_types=1);

namespace Deployer;

require dirname(__DIR__) . '/src/functions.php';
require_once 'recipe/common.php';
require_once 'recipe/composer.php';

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

register_view_file_task('.env', 'env');
register_download_file_task('.env', 'env');
register_view_file_task('auth.json');
register_download_file_task('auth.json');

after('deploy:failed', 'deploy:unlock');
