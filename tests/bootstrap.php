<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Cleanup previously created Symfony container and logs
$rmDir = function ($path) {
    if (!file_exists($path) || !is_dir($path)) {
        return;
    }
    $dh = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    /** @var \SplFileInfo $file */
    foreach ($dh as $file) {
        if (!$file->isFile() && !$file->isLink()) {
            continue;
        }
        unlink($file->getPathname());
    }
    $dh = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($dh as $file) {
        rmdir($file);
    }
    rmdir($path);
};
$rmDir(__DIR__ . '/Fixtures/cache');
$rmDir(__DIR__ . '/Fixtures/logs');
unset($rmDir);
