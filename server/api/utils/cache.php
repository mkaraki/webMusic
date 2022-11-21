<?php
require_once __DIR__ . '../../../_config.php';

function isFileInTransformedStore(string $name): bool
{
    global $transformedFileDir;
    return is_file($transformedFileDir . '/' . $name);
}

function readFileInTransformedStore(string $name): string
{
    global $transformedFileDir;
    return file_get_contents($transformedFileDir . '/' . $name);
}

function writeFileToTransformedStore(string $name, string $content)
{
    global $transformedFileDir;
    file_put_contents($transformedFileDir . '/' . $name, $content);
    try {
    } catch (Error) {
    }
}
