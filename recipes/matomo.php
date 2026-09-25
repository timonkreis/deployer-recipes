<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Deployer;

require_once __DIR__ . '/common.php';

set('shared_files', ['config.ini.php']);
set('writable_dirs', ['vendor/matomo/matomo/matomo/tmp']);

register_view_file_task('config', 'config.ini.php');
register_download_file_task('config', 'config.ini.php');
