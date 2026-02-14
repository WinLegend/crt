<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$host = $_ENV['DB_HOST'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASS'];
$name = $_ENV['DB_NAME'];

$backupDir = __DIR__ . '/../backups';
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$filename = "backup-" . date('Y-m-d-H-i-s') . ".sql";
$command = "mysqldump -u $user -p$pass -h $host $name > $backupDir/$filename";

system($command, $output);
echo "Backup created: $filename\n";

// Delete backups older than 7 days
$files = glob($backupDir . "/*.sql");
$now = time();

foreach ($files as $file) {
    if (is_file($file)) {
        if ($now - filemtime($file) >= 60 * 60 * 24 * 7) { // 7 days
            unlink($file);
            echo "Deleted old backup: $file\n";
        }
    }
}
