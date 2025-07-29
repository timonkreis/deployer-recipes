<?php
declare(strict_types=1);

/**
 * @param string $file
 * @return array
 * @throws JsonException
 */
function load_json_from_file(string $file): array {
    if (!($content = file_get_contents(project_root() . '/' . $file))) {
        throw new \RuntimeException('Unable to read JSON file');
    }

    return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
}

/**
 * @return string
 */
function project_root(): string {
    return dirname(__DIR__, 4);
}
