<?php
declare(strict_types=1);

use function Deployer\ask;
use function Deployer\askConfirmation;
use function Deployer\desc;
use function Deployer\download;
use function Deployer\task;

/**
 * @param string $file
 * @return array
 * @throws JsonException
 */
function load_json_from_file(string $file): array {
    if (!($content = file_get_contents(project_root() . '/' . $file))) {
        throw new \RuntimeException('Unable to read JSON file "' . $file . '"');
    }

    return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
}

/**
 * @return string
 */
function project_root(): string {
    return dirname(__DIR__, 4);
}

/**
 * @throws Exception
 */
function register_view_file_task(string $file, ?string $taskName = null): void {
    desc('View ' . $file);
    task('view:' . ($taskName ?? $file), function() use ($file): void {
        $destination = tempnam(sys_get_temp_dir(), $file);

        download('{{current_path}}/' . $file, $destination, ['flags' => '-azLP']);
        readfile($destination);
        unlink($destination);
    });
}

/**
 * @throws Exception
 */
function register_download_file_task(string $file, ?string $taskName = null): void {
    desc('Download ' . $file);
    task('download:' . ($taskName ?? $file), function() use ($file): void {
        if (!askConfirmation('Do you want to download the file "' . $file . '"?', true)) {
            return;
        }

        $name = @is_file(project_root() . '/' . $file)
            ? ask('The file already exists. Do you want to rename the downloaded file?', $file)
            : $file;

        download('{{current_path}}/' . $file, $name, ['flags' => '-azLP']);
    });
}
