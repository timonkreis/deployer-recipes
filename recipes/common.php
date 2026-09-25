<?php
/** @noinspection PhpUnhandledExceptionInspection */
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
set('docroot', 'public');

set('repository', static function(): string {
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

desc('Copy custom .user.ini file');
task('deploy:user.ini', static function(): void {
    $file = '{{docroot}}/'. get('user.ini', '.user.ini');

    if (!test('[ -f {{deploy_path}}/shared/' . $file . ' ]')) {
        throw error(sprintf('Required .user.ini file (%s) does not exist', '{{deploy_path}}/shared/' . $file));
    }

    run(sprintf('cp {{deploy_path}}/shared/%1$s {{release_path}}/%1$s', $file));
});
