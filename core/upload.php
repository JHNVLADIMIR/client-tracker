<?php
declare(strict_types=1);

function saveAutomationImage(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
        || empty($file['tmp_name'])
        || !is_string($file['tmp_name'])
        || !is_uploaded_file($file['tmp_name'])
        || !isset($file['size'])
        || (int) $file['size'] > 2 * 1024 * 1024) {
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $extensions = [
        'image/jpeg' => '.jpg',
        'image/png' => '.png',
        'image/webp' => '.webp',
    ];
    if (!isset($extensions[$mime])) {
        return null;
    }

    $projectRoot = dirname(__DIR__);
    $uploadDirectory = $projectRoot . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0775, true) && !is_dir($uploadDirectory)) {
        return null;
    }

    $htaccess = $uploadDirectory . DIRECTORY_SEPARATOR . '.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "Options -Indexes\n\n<FilesMatch \"\\.(php|phtml|phar|php3|php4|php5|php7|php8)$\">\n    Require all denied\n</FilesMatch>\n");
    }

    $filename = bin2hex(random_bytes(12)) . $extensions[$mime];
    $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return null;
    }
    return 'uploads/' . $filename;
}
