<?php

namespace Nesneek\Controllers;

use Nesneek\Models\User;
use Nesneek\Models\File;
use Nesneek\Config\Database;
use Nesneek\Utils\Auth;
use PDO;

class AdminController {
    private $db;
    private $user;
    private $file;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
        $this->file = new File($this->db);
    }

    private function checkAdmin() {
        $userData = Auth::validateToken();
        if (!$userData || $userData['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(["message" => "Access denied"]);
            exit;
        }
        return $userData;
    }

    public function getUsers() {
        $this->checkAdmin();
        $stmt = $this->user->getAll();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
    }

    public function toggleBlock($id) {
        $this->checkAdmin();
        if ($this->user->toggleBlock($id)) {
            echo json_encode(["message" => "User status updated"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to update user"]);
        }
    }

    public function getFiles() {
        $this->checkAdmin();
        $stmt = $this->file->getAll();
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($files);
    }

    public function deleteFile($id) {
        $this->checkAdmin();
        // Get file to delete from disk
        $fileData = $this->file->getById($id);
        if ($fileData) {
            if (file_exists($fileData['upload_path'])) {
                unlink($fileData['upload_path']);
            }
            $this->file->markDeleted($id);
            echo json_encode(["message" => "File deleted"]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "File not found"]);
        }
    }

    public function generateInvites() {
        $this->checkAdmin();
        $data = json_decode(file_get_contents("php://input"));
        $count = $data->count ?? 10;
        
        $invites = [];
        $query = "INSERT INTO invites (code, created_by, created_at) VALUES (:code, :created_by, NOW())";
        $stmt = $this->db->prepare($query);
        
        // Assuming admin ID is 1 or from token
        $userData = Auth::validateToken();
        
        for ($i = 0; $i < $count; $i++) {
            $code = bin2hex(random_bytes(6)); // 12 chars
            $stmt->execute([':code' => $code, ':created_by' => $userData['id']]);
            $invites[] = ['code' => $code];
        }
        
        echo json_encode(["message" => "$count invites generated", "invites" => $invites]);
    }

    public function getStats() {
        $this->checkAdmin();
        
        $users = $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $files = $this->db->query("SELECT COUNT(*) FROM files")->fetchColumn();
        $invites = $this->db->query("SELECT COUNT(*) FROM invites WHERE is_used = 1")->fetchColumn();
        
        echo json_encode([
            "users" => $users,
            "files" => $files,
            "used_invites" => $invites
        ]);
    }
}
