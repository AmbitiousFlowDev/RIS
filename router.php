<?php

require_once __DIR__ . '/utils/Security.php';

$requestedPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$requestedPath = rawurldecode($requestedPath);
$staticFile = realpath(__DIR__ . $requestedPath);
$publicRoot = realpath(__DIR__);

$allowedStaticExtensions = [
    'css',
    'js',
    'mjs',
    'png',
    'jpg',
    'jpeg',
    'gif',
    'svg',
    'webp',
    'ico',
    'woff',
    'woff2',
    'ttf',
    'map',
    'txt',
    'xml',
];

if ($staticFile !== false && $publicRoot !== false) {
    $staticExtension = strtolower(pathinfo($staticFile, PATHINFO_EXTENSION));
    $isInsideProject = str_starts_with($staticFile, $publicRoot . DIRECTORY_SEPARATOR) || $staticFile === $publicRoot;

    if (is_file($staticFile) && $isInsideProject && in_array($staticExtension, $allowedStaticExtensions, true)) {
        Security::serveStaticFile($staticFile, $staticExtension);
    }
}

require __DIR__ . '/index.php';
