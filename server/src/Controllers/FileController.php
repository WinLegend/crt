<?php

namespace Nesneek\Controllers;

use Nesneek\Models\File;
use Nesneek\Config\Database;
use Nesneek\Utils\Auth;
use PDO;

class FileController {
    private $db;
    private $file;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->file = new File($this->db);
    }

    public function upload() {
        $userData = Auth::validateToken();
        if (!$userData) {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized"]);
            return;
        }

        if (!isset($_FILES['file'])) {
            http_response_code(400);
            echo json_encode(["message" => "No file uploaded"]);
            return;
        }

        $file = $_FILES['file'];
        $expiry_days = $_POST['expiry_days'] ?? 1;

        // Validation
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'zip', 'txt', 'docx']; // Add more as needed
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        // Simple mime check or extension check
        // For security, rename file and store safely
        
        $unique_token = bin2hex(random_bytes(16));
        $new_filename = $unique_token . '.' . $ext;
        
        // Create user directory
        $target_dir = __DIR__ . "/../../uploads/" . $userData['id'] . "/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $this->file->user_id = $userData['id'];
            $this->file->filename = $new_filename;
            $this->file->original_name = $file['name'];
            $this->file->size = $file['size'];
            $this->file->upload_path = $target_file;
            $this->file->expires_at = date('Y-m-d H:i:s', strtotime("+$expiry_days days"));
            $this->file->unique_token = $unique_token;

            if($this->file->create()) {
                http_response_code(201);
                echo json_encode([
                    "message" => "File uploaded successfully",
                    "file" => [
                        "url" => "/files/download/" . $unique_token,
                        "original_name" => $file['name'],
                        "expires_at" => $this->file->expires_at
                    ]
                ]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to save file record"]);
            }
        } else {
            http_response_code(500);
            echo json_encode(["message" => "File upload failed"]);
        }
    }

    public function download($token) {
        $fileData = $this->file->getByToken($token);

        if(!$fileData || $fileData['is_deleted']) {
            http_response_code(404);
            echo json_encode(["message" => "File not found or expired"]);
            return;
        }

        if(strtotime($fileData['expires_at']) < time()) {
            $this->file->markDeleted($fileData['id']);
            http_response_code(410);
            echo json_encode(["message" => "File expired"]);
            return;
        }

        $filepath = $fileData['upload_path'];
        if(file_exists($filepath)) {
            // Increment download count
            $this->file->incrementDownload($fileData['id']);
            
            // Serve file
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($fileData['original_name']).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            
            // Mark deleted after download logic? User requirement: "after downloading file and record in DB marked as deleted"
            // Let's implement that.
            $this->file->markDeleted($fileData['id']);
            // Delete file physically? Maybe via cron or immediately.
            // unlink($filepath); 
            exit;
        } else {
            http_response_code(404);
            echo json_encode(["message" => "File not found on server"]);
        }
    }
    
    public function myFiles() {
        $userData = Auth::validateToken();
        if (!$userData) {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized"]);
            return;
        }
        
        $stmt = $this->file->getByUser($userData['id']);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($files);
    }
}
