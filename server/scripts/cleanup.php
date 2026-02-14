<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Nesneek\Config\Database;
use Nesneek\Models\File;

$database = new Database();
$db = $database->getConnection();
$fileModel = new File($db);

$query = "SELECT * FROM files WHERE expires_at < NOW() AND is_deleted = 0";
$stmt = $db->prepare($query);
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (file_exists($row['upload_path'])) {
        unlink($row['upload_path']);
    }
    $fileModel->markDeleted($row['id']);
    echo "Deleted expired file: " . $row['filename'] . "\n";
}
