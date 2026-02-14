<?php

namespace Nesneek\Models;

use PDO;

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password_hash;
    public $invite_code;
    public $is_blocked;
    public $role;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET username=:username, password_hash=:password_hash, invite_code=:invite_code, created_at=NOW()";
        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->password_hash = htmlspecialchars(strip_tags($this->password_hash));
        $this->invite_code = htmlspecialchars(strip_tags($this->invite_code));

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":invite_code", $this->invite_code);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function usernameExists() {
        $query = "SELECT id, username, password_hash, role, is_blocked FROM " . $this->table_name . " WHERE username = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->username);
        $stmt->execute();
        $num = $stmt->rowCount();

        if($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->password_hash = $row['password_hash'];
            $this->role = $row['role'];
            $this->is_blocked = $row['is_blocked'];
            return true;
        }
        return false;
    }

    public function getAll() {
        $query = "SELECT id, username, role, is_blocked, created_at FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function toggleBlock($id) {
        // First check status
        $query = "SELECT is_blocked FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $newStatus = $row['is_blocked'] ? 0 : 1;
        
        $updateQuery = "UPDATE " . $this->table_name . " SET is_blocked = :status WHERE id = :id";
        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->bindParam(':status', $newStatus);
        $updateStmt->bindParam(':id', $id);
        
        return $updateStmt->execute();
    }
}
