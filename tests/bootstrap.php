<?php
require_once __DIR__ . '/../vendor/autoload.php';

$fs = new \Symfony\Component\Filesystem\Filesystem();
$fs->remove(__DIR__ . '/Fixtures/cache');
$fs->remove(__DIR__ . '/Fixtures/logs');
