<?php
declare(strict_types=1);

namespace Deployer;

require_once __DIR__ . '/common.php';

set('shared_files', ['config.ini.php']);
set('writable_dirs', ['vendor/matomo/matomo/matomo/tmp']);

desc('View config.ini.php');
task('view:config', function(): void {
    $destination = tempnam(sys_get_temp_dir(), 'config.ini.php');

    download('{{current_path}}/config.ini.php', $destination, ['flags' => '-azLP']);
    readfile($destination);
    unlink($destination);
});

desc('Download config.ini.php');
task('download:config', function(): void {
    if (!askConfirmation('Do you want to download the file "config.ini.php"?', true)) {
        return;
    }

    $name = @is_file(project_root() . '/config.ini.php')
        ? ask('The file already exists. Do you want to rename the downloaded file?', 'config.ini.php')
        : 'config.ini.php';

    download('{{current_path}}/config.ini.php', $name, ['flags' => '-azLP']);
});
