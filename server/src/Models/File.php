<?php

namespace Nesneek\Models;

use PDO;

class File {
    private $conn;
    private $table_name = "files";

    public $id;
    public $user_id;
    public $filename;
    public $original_name;
    public $size;
    public $upload_path;
    public $expires_at;
    public $unique_token;
    public $download_count;
    public $is_deleted;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET user_id=:user_id, filename=:filename, original_name=:original_name, size=:size, upload_path=:upload_path, expires_at=:expires_at, unique_token=:unique_token, created_at=NOW()";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":filename", $this->filename);
        $stmt->bindParam(":original_name", $this->original_name);
        $stmt->bindParam(":size", $this->size);
        $stmt->bindParam(":upload_path", $this->upload_path);
        $stmt->bindParam(":expires_at", $this->expires_at);
        $stmt->bindParam(":unique_token", $this->unique_token);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getByToken($token) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE unique_token = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $token);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getByUser($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = ? AND is_deleted = 0 ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        return $stmt;
    }

    public function incrementDownload($id) {
        $query = "UPDATE " . $this->table_name . " SET download_count = download_count + 1 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }
    
    public function markDeleted($id) {
        $query = "UPDATE " . $this->table_name . " SET is_deleted = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }
    
    public function getAll() {
        $query = "SELECT f.*, u.username FROM " . $this->table_name . " f LEFT JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
